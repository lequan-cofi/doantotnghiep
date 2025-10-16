<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for agent's assigned properties
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get assigned property IDs for the agent
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
        
        if (empty($assignedPropertyIds)) {
            $reviews = collect();
            $stats = [
                'total' => 0,
                'avg_rating' => 0,
                'pending_reply' => 0,
                'replied' => 0,
                'recent' => 0
            ];
        } else {
        // Get all reviews for assigned properties with optimized query
        $query = Review::select([
            'reviews.*',
            'units.code as unit_name',
            'properties.name as property_name',
            DB::raw("CONCAT_WS(', ', locations.street, locations.ward, locations.district, locations.city) as location_address"),
            DB::raw("CONCAT_WS(', ', locations2025.street, locations2025.ward, locations2025.city) as location2025_address"),
            'leases.contract_no as lease_contract_number',
            'tenants.full_name as tenant_name',
            'tenants.phone as tenant_phone',
            DB::raw('(SELECT COUNT(*) FROM review_replies WHERE review_replies.review_id = reviews.id AND review_replies.deleted_at IS NULL) as replies_count')
        ])
        ->leftJoin('units', 'reviews.unit_id', '=', 'units.id')
        ->leftJoin('properties', 'units.property_id', '=', 'properties.id')
        ->leftJoin('locations', 'properties.location_id', '=', 'locations.id')
        ->leftJoin('locations as locations2025', 'properties.location_id_2025', '=', 'locations2025.id')
        ->leftJoin('leases', 'reviews.lease_id', '=', 'leases.id')
        ->leftJoin('users as tenants', 'reviews.tenant_id', '=', 'tenants.id')
        ->whereIn('properties.id', $assignedPropertyIds)
        ->whereNull('reviews.deleted_at');

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('properties.name', 'like', "%{$search}%")
                      ->orWhere('units.code', 'like', "%{$search}%")
                      ->orWhere('reviews.title', 'like', "%{$search}%")
                      ->orWhere('tenants.full_name', 'like', "%{$search}%");
                });
            }

            // Apply rating filter
            if ($request->filled('rating') && $request->rating !== '') {
                $rating = $request->rating;
                $query->where('reviews.overall_rating', $rating);
            }

            // Apply status filter
            if ($request->filled('status') && $request->status !== 'all') {
                $status = $request->status;
                if ($status === 'pending_reply') {
                    $query->whereDoesntHave('allReplies');
                } elseif ($status === 'replied') {
                    $query->whereHas('allReplies');
                } elseif ($status === 'recent') {
                    $query->where('reviews.created_at', '>=', now()->subDays(7));
                }
            }

            $reviews = $query->latest('reviews.created_at')->paginate(20);

            // Calculate statistics
            $stats = $this->calculateReviewStats($assignedPropertyIds);
        }

        return view('agent.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Display the specified review
     */
    public function show($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get assigned property IDs for the agent
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
        
        $review = Review::with([
            'unit.property.location',
            'unit.property.location2025',
            'unit.property.owner',
            'lease',
            'tenant',
            'replies.user',
            'replies.childReplies.user'
        ])
        ->whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->where('id', $id)
        ->whereNull('deleted_at')
        ->firstOrFail();

        return view('agent.reviews.show', compact('review'));
    }

    /**
     * Store a reply to a review
     */
    public function storeReply(Request $request, $id)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get assigned property IDs for the agent
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
            
            // Get review and verify agent has access
            $review = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
                $query->whereIn('properties.id', $assignedPropertyIds);
            })
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();

            // Validate input
            $validated = $request->validate([
                'content' => 'required|string|min:10|max:1000',
                'parent_reply_id' => 'nullable|exists:review_replies,id'
            ], [
                'content.required' => 'Vui lòng nhập nội dung phản hồi',
                'content.min' => 'Nội dung phản hồi phải có ít nhất 10 ký tự',
                'content.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự',
            ]);

            // Determine user type based on role
            $userType = 'agent'; // Agent replies as agent

            // Create reply
            $reply = ReviewReply::create([
                'review_id' => $review->id,
                'user_id' => $user->id,
                'parent_reply_id' => $validated['parent_reply_id'] ?? null,
                'content' => $validated['content'],
                'user_type' => $userType
            ]);

            // Load the reply with user relationship
            $reply->load('user');

            Log::info('Agent review reply created:', [
                'reply_id' => $reply->id,
                'review_id' => $review->id,
                'agent_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phản hồi đã được gửi thành công!',
                'reply' => [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'user_name' => $user->full_name ?? $user->name,
                    'user_type' => $reply->user_type_label,
                    'created_at' => $reply->created_at->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Agent review reply creation error:', [
                'message' => $e->getMessage(),
                'review_id' => $id,
                'agent_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a reply
     */
    public function updateReply(Request $request, $reviewId, $replyId)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get assigned property IDs for the agent
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
            
            // Get review and verify agent has access
            $review = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
                $query->whereIn('properties.id', $assignedPropertyIds);
            })
            ->where('id', $reviewId)
            ->whereNull('deleted_at')
            ->firstOrFail();

            // Get reply and verify it belongs to the agent
            $reply = ReviewReply::where('id', $replyId)
                ->where('user_id', $user->id)
                ->where('review_id', $review->id)
                ->firstOrFail();

            // Check if reply can be edited
            if (!$reply->canBeEditedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể chỉnh sửa phản hồi sau 24 giờ!'
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'content' => 'required|string|min:10|max:1000',
            ], [
                'content.required' => 'Vui lòng nhập nội dung phản hồi',
                'content.min' => 'Nội dung phản hồi phải có ít nhất 10 ký tự',
                'content.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự',
            ]);

            // Update reply
            $reply->update([
                'content' => $validated['content']
            ]);

            Log::info('Agent review reply updated:', [
                'reply_id' => $reply->id,
                'review_id' => $review->id,
                'agent_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phản hồi đã được cập nhật thành công!',
                'reply' => [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'user_name' => $user->full_name ?? $user->name,
                    'user_type' => $reply->user_type_label,
                    'created_at' => $reply->created_at->format('d/m/Y H:i'),
                    'updated_at' => $reply->updated_at->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Agent review reply update error:', [
                'message' => $e->getMessage(),
                'review_id' => $reviewId,
                'reply_id' => $replyId,
                'agent_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a reply
     */
    public function deleteReply($reviewId, $replyId)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get assigned property IDs for the agent
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
            
            // Get review and verify agent has access
            $review = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
                $query->whereIn('properties.id', $assignedPropertyIds);
            })
            ->where('id', $reviewId)
            ->whereNull('deleted_at')
            ->firstOrFail();

            // Get reply and verify it belongs to the agent
            $reply = ReviewReply::where('id', $replyId)
                ->where('user_id', $user->id)
                ->where('review_id', $review->id)
                ->firstOrFail();

            // Check if reply can be deleted
            if (!$reply->canBeDeletedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa phản hồi này!'
                ], 403);
            }

            // Soft delete the reply
            $reply->delete();

            Log::info('Agent review reply deleted:', [
                'reply_id' => $reply->id,
                'review_id' => $review->id,
                'agent_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phản hồi đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Agent review reply deletion error:', [
                'message' => $e->getMessage(),
                'review_id' => $reviewId,
                'reply_id' => $replyId,
                'agent_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews data for AJAX requests
     */
    public function getReviewsData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get assigned property IDs for the agent
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
        
        if (empty($assignedPropertyIds)) {
            return response()->json([
                'reviews' => [],
                'stats' => [
                    'total' => 0,
                    'avg_rating' => 0,
                    'pending_reply' => 0,
                    'replied' => 0,
                    'recent' => 0
                ]
            ]);
        }

        $query = Review::with([
            'unit.property',
            'tenant',
            'replies.user'
        ])
        ->whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at');

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'pending_reply') {
                $query->whereDoesntHave('allReplies');
            } elseif ($status === 'replied') {
                $query->whereHas('allReplies');
            } elseif ($status === 'recent') {
                $query->where('created_at', '>=', now()->subDays(7));
            }
        }

        if ($request->filled('rating') && $request->rating !== '') {
            $query->where('overall_rating', $request->rating);
        }

        $limit = $request->get('limit', 100);
        $reviews = $query->latest('created_at')->limit($limit)->get();

        $stats = $this->calculateReviewStats($assignedPropertyIds);

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    /**
     * Calculate review statistics for assigned properties
     */
    private function calculateReviewStats($assignedPropertyIds)
    {
        if (empty($assignedPropertyIds)) {
            return [
                'total' => 0,
                'avg_rating' => 0,
                'pending_reply' => 0,
                'replied' => 0,
                'recent' => 0
            ];
        }

        $total = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at')
        ->count();

        $avgRating = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at')
        ->avg('overall_rating');

        $pendingReply = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at')
        ->whereDoesntHave('allReplies')
        ->count();

        $replied = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at')
        ->whereHas('allReplies')
        ->count();

        $recent = Review::whereHas('unit.property', function($query) use ($assignedPropertyIds) {
            $query->whereIn('properties.id', $assignedPropertyIds);
        })
        ->whereNull('deleted_at')
        ->where('created_at', '>=', now()->subDays(7))
        ->count();

        return [
            'total' => $total,
            'avg_rating' => round($avgRating, 1),
            'pending_reply' => $pendingReply,
            'replied' => $replied,
            'recent' => $recent
        ];
    }
}
