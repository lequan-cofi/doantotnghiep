<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = Auth::user();
        $userProfile = $user->userProfile;
        return view('tenant.profile.index', compact('user', 'userProfile'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $request->validate([
            // Basic user info
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users')->ignore($user->id)
            ],
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            
            // KYC profile info
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'id_number' => 'nullable|string|max:50',
            'id_issued_at' => 'nullable|date|before_or_equal:today',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            // Basic validation messages
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'phone.max' => 'Số điện thoại không được vượt quá 30 ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            
            // KYC validation messages
            'dob.date' => 'Ngày sinh không hợp lệ.',
            'dob.before' => 'Ngày sinh phải trước ngày hiện tại.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'id_number.max' => 'Số CMND/CCCD không được vượt quá 50 ký tự.',
            'id_issued_at.date' => 'Ngày cấp CMND/CCCD không hợp lệ.',
            'id_issued_at.before_or_equal' => 'Ngày cấp CMND/CCCD không được sau ngày hiện tại.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        try {
            // Prepare update data
            $updateData = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                // Verify current password if provided
                if ($request->filled('current_password')) {
                    if (!Hash::check($request->current_password, $user->password_hash)) {
                        return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
                    }
                }

                $updateData['password_hash'] = Hash::make($request->password);
            }

            // Update user using DB facade
            DB::table('users')
                ->where('id', $user->id)
                ->update($updateData);

            // Update or create user profile
            $profileData = [
                'dob' => $request->dob,
                'gender' => $request->gender,
                'id_number' => $request->id_number,
                'id_issued_at' => $request->id_issued_at,
                'address' => $request->address,
                'note' => $request->note,
            ];

            // Remove null values
            $profileData = array_filter($profileData, function($value) {
                return $value !== null && $value !== '';
            });

            if (!empty($profileData)) {
                $userProfile = $user->userProfile;
                if ($userProfile) {
                    DB::table('user_profiles')
                        ->where('user_id', $user->id)
                        ->update($profileData);
                } else {
                    $profileData['user_id'] = $user->id;
                    DB::table('user_profiles')->insert($profileData);
                }
            }

            return redirect()->route('tenant.profile')
                ->with('success', 'Cập nhật thông tin thành công.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật thông tin. Vui lòng thử lại.']);
        }
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        $userProfile = $user->userProfile;
        return view('tenant.profile.edit', compact('user', 'userProfile'));
    }
}
