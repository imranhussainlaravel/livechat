<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(private AuthService $auth) {}

    /**
     * GET /login — Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * POST /login — Authenticate and redirect to role-based dashboard.
     */
    public function login(LoginRequest $request)
    {
        $result = $this->auth->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->intended($result['redirect_to']);
    }

    /**
     * POST /logout — Destroy session and redirect to login.
     */
    public function logout(Request $request)
    {
        $this->auth->logout();

        return redirect('/login');
    }
}
