<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // if (!view()->exists('auth.login')) {
        //     abort(404);
        // }
        return view('auth.login');
    }

    /**
     * Handle login with rate limiting
     */
    public function login(LoginRequest $request)
    {
        $throttleKey = $this->throttleKey($request);

        // Check rate limiting
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'email' => [__('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])],
            ]);
        }

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 300); // 5 minute decay

            throw ValidationException::withMessages([
                'email' => [__('These credentials do not match our records.')],
            ]);
        }

        // Clear rate limiter on success
        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        // Update last login timestamp
        $this->userRepository->updateLastLogin(Auth::user());

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign default Developer role
        $user->assignRole(Role::DEVELOPER->value);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully. Welcome!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Get throttle key for rate limiting
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }
}
