<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function forgotPassword(Request $request)
    {
        // 1. Validate email
        $request->validate([
            'email' => 'required|email',
        ]);

        // 2. Tìm user theo email và role 'client'
        $user = User::where('email', $request->email)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Customer');
            })
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 404,
                'message' => 'Email không tồn tại hoặc không phải client'
            ], 404);
        }

        // 3. Sinh OTP
        $otp = rand(100000, 999999);

        // 4. Lưu OTP (phải có cột `otp` trong bảng users)
        $user->otp = $otp;
        $user->save();

        // 5. Gửi mail
        try {
            Mail::raw("Mã OTP để đổi mật khẩu: $otp", function($msg) use ($user) {
                $msg->to($user->email);
                $msg->subject('Quên mật khẩu - Client');
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Lỗi khi gửi mail',
                'error'   => $e->getMessage()
            ], 500);
        }

        // 6. Trả về JSON
        return response()->json([
            'status'  => 200,
            'message' => 'OTP đã được gửi đến email của bạn'
        ]);
    }

    public function resetPassword(Request $request)
    {
        // 1. Validate dữ liệu
        $request->validate([
            'email'        => 'required|email',
            'otp'          => 'required',
            'new_password' => 'required|min:6'
        ]);

        // 2. Tìm user theo email, otp và role client
        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Customer');
            })
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 400,
                'message' => 'OTP hoặc Email không đúng'
            ], 400);
        }

        // 3. Đổi mật khẩu và xóa OTP
        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->save();

        return response()->json([
            'status'  => 200,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }
}
