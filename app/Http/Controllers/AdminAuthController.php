<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAuthController extends ApiController
{
    /**
     * POST /api/v1/admin/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return $this->success([
            'admin' => [
                'id'       => $admin->id,
                'name'     => $admin->name,
                'email'    => $admin->email,
                'is_super' => $admin->is_super,
            ],
            'token' => $token,
        ]);
    }

    /**
     * POST /api/v1/admin/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out');
    }
}
