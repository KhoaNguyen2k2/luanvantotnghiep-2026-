<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function showLoginForm(Request $request)
    {
        $loginType = $request->routeIs('login.staff') ? 'staff' : ($request->routeIs('login.customer') ? 'customer' : null);

        if ($loginType === null) {
            return view('auth.login-choice');
        }

        return view('auth.login', compact('loginType'));
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => ['required', 'string'],
            'password' => ['required', 'string'],
            'login_type' => ['required', 'in:customer,staff'],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        $loginType = $request->input('login_type');

        if ($loginType === 'staff' && ! in_array($user->utype, ['ADM', 'ADMM'], true)) {
            Auth::logout();
            throw ValidationException::withMessages([
                $this->username() => 'Tài khoản này không phải tài khoản nhân viên.',
            ]);
        }

        if ($loginType === 'staff' && $user->utype === 'ADMM' && strtolower($user->email) !== 'admint@lvtn.vn') {
            Auth::logout();
            throw ValidationException::withMessages([
                $this->username() => 'Tài khoản admin tổng không hợp lệ.',
            ]);
        }

        if ($loginType === 'customer' && $user->utype !== 'USR') {
            Auth::logout();
            throw ValidationException::withMessages([
                $this->username() => 'Tài khoản này không phải tài khoản khách hàng.',
            ]);
        }
    }

    protected function redirectTo()
    {
        if (Auth::check() && in_array(Auth::user()->utype, ['ADM', 'ADMM'], true)) {
            return route('admin.index');
        }

        return route('user.account.details');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
