<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = \App\Models\Admin::where('email', $request->email)->first();

        if ($admin && \Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
            session(['admin_email' => $admin->email]);
            return redirect('/admin/dashboard');
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function conversations()
    {
        return view('admin.conversations');
    }

    public function conversation($id)
    {
        return view('admin.conversation', compact('id'));
    }
}
