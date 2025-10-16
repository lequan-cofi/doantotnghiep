<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return view('manager.reviews.index', [
                'reviews' => collect([]),
                'units' => collect([]),
                'tenants' => collect([])
            ])->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $query = Review::with([
            'unit.property',
            'lease.tenant',
            'tenant',
            'organization'
        ])->where('organization_id', $managerOrganization->id);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('rating_min')) {
            $query->where('overall_rating', '>=', $request->rating_min);
        }

        if ($request->filled('rating_max')) {
            $query->where('overall_rating', '<=', $request->rating_max);
        }

        if ($request->filled('recommend')) {
            $query->where('recommend', $request->recommend);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('full_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function($unitQuery) use ($search) {
                      $unitQuery->where('code', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options
        $units = Unit::with('property')
            ->whereHas('property', function($q) use ($managerOrganization) {
                $q->where('organization_id', $managerOrganization->id);
            })
            ->get();
        
        $tenants = User::whereHas('organizationRoles', function($q) use ($managerOrganization) {
            $q->where('organization_id', $managerOrganization->id)
              ->whereIn('key_code', ['tenant']);
        })->get();

        return view('manager.reviews.index', compact(
            'reviews',
            'units',
            'tenants'
        ));
    }

    /**
     * Display the specified review.
     */
    public function show($id)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return redirect()->route('manager.reviews.index')
                ->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $review = Review::with([
            'unit.property',
            'lease.tenant',
            'tenant',
            'replies.user',
            'organization'
        ])->where('organization_id', $managerOrganization->id)
          ->findOrFail($id);

        return view('manager.reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit($id)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return redirect()->route('manager.reviews.index')
                ->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $review = Review::with([
            'unit.property',
            'lease.tenant',
            'tenant'
        ])->where('organization_id', $managerOrganization->id)
          ->findOrFail($id);

        return view('manager.reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, $id)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }
            return back()->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $review = Review::where('organization_id', $managerOrganization->id)
                       ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:published,hidden',
            'overall_rating' => 'nullable|numeric|min:1|max:5',
            'location_rating' => 'nullable|numeric|min:1|max:5',
            'quality_rating' => 'nullable|numeric|min:1|max:5',
            'service_rating' => 'nullable|numeric|min:1|max:5',
            'price_rating' => 'nullable|numeric|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'recommend' => 'nullable|in:yes,maybe,no',
        ]);

        try {
            DB::beginTransaction();

            $review->update([
                'status' => $request->status,
                'overall_rating' => $request->overall_rating,
                'location_rating' => $request->location_rating,
                'quality_rating' => $request->quality_rating,
                'service_rating' => $request->service_rating,
                'price_rating' => $request->price_rating,
                'title' => $request->title,
                'content' => $request->content,
                'recommend' => $request->recommend,
            ]);

            DB::commit();

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đánh giá đã được cập nhật thành công!',
                    'redirect' => route('manager.reviews.show', $review->id)
                ]);
            }

            return redirect()->route('manager.reviews.show', $review->id)
                ->with('success', 'Đánh giá đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error for debugging
            Log::error('Review update error: ' . $e->getMessage(), [
                'review_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật đánh giá: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Không thể cập nhật đánh giá: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy($id)
    {
        try {
            // Lấy tổ chức của manager hiện tại
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations()->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }

            $review = Review::where('organization_id', $managerOrganization->id)
                           ->findOrFail($id);
            
            // Soft delete the review
            $review->deleted_by = Auth::id();
            $review->save();
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Review delete error: ' . $e->getMessage(), [
                'review_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đánh giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add reply to review
     */
    public function addReply(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        try {
            // Lấy tổ chức của manager hiện tại
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations()->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }

            $review = Review::where('organization_id', $managerOrganization->id)
                           ->findOrFail($id);

            $reply = $review->replies()->create([
                'content' => $request->content,
                'user_id' => Auth::id(),
                'user_type' => 'manager',
            ]);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phản hồi đã được thêm thành công!',
                    'reply' => $reply->load('user')
                ]);
            }

            return back()->with('success', 'Phản hồi đã được thêm thành công!');

        } catch (\Exception $e) {
            Log::error('Review reply error: ' . $e->getMessage(), [
                'review_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thêm phản hồi: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Không thể thêm phản hồi: ' . $e->getMessage());
        }
    }

    /**
     * Get review statistics
     */
    public function getStatistics()
    {
        try {
            // Lấy tổ chức của manager hiện tại
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations()->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }

            $stats = DB::table('reviews')
                ->where('organization_id', $managerOrganization->id)
                ->whereNull('deleted_at')
                ->selectRaw('
                    COUNT(*) as total_reviews,
                    AVG(overall_rating) as average_rating,
                    COUNT(CASE WHEN status = "published" THEN 1 END) as published_reviews,
                    COUNT(CASE WHEN status = "hidden" THEN 1 END) as hidden_reviews,
                    COUNT(CASE WHEN recommend = "yes" THEN 1 END) as recommend_yes,
                    COUNT(CASE WHEN recommend = "maybe" THEN 1 END) as recommend_maybe,
                    COUNT(CASE WHEN recommend = "no" THEN 1 END) as recommend_no
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Review statistics error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }
}
