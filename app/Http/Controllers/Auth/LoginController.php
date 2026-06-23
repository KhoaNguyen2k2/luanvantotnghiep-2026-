<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

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

    public function redirectToProvider(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        $driver = Socialite::driver($provider);

        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        return $driver->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            return redirect()
                ->route('login.customer')
                ->withErrors(['email' => 'Không thể đăng nhập bằng ' . ucfirst($provider) . '. Vui lòng thử lại.']);
        }

        if (! $socialUser->getEmail()) {
            return redirect()
                ->route('login.customer')
                ->withErrors(['email' => ucfirst($provider) . ' chưa cung cấp email cho tài khoản này.']);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user && $user->utype !== 'USR') {
            return redirect()
                ->route('login.customer')
                ->withErrors(['email' => 'Email này không thuộc tài khoản khách hàng.']);
        }

        if (! $user) {
            $providerId = (string) ($socialUser->getId() ?: Str::uuid());
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Khách hàng',
                'email' => $socialUser->getEmail(),
                'mobile' => 'social_' . $provider . '_' . Str::limit($providerId, 32, ''),
                'utype' => 'USR',
                'password' => Hash::make(Str::random(32)),
            ]);

            $user->email_verified_at = now();
            $user->save();
        } elseif (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        Auth::login($user, true);

        return redirect()->route('user.account.details');
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
