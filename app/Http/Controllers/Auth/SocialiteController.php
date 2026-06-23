<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class SocialiteController extends Controller
{
   public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        // Kiểm tra xem user đã tồn tại trong DB chưa
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Đăng nhập nếu user đã tồn tại
            Auth::login($user);
        } else {
            // Tạo tài khoản mới nếu user chưa tồn tại
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // Tạo mật khẩu ngẫu nhiên
            ]);

            Auth::login($user);
        }

        return redirect()->intended('/home'); // Chuyển hướng tới trang chủ/dashboard
    }
}
