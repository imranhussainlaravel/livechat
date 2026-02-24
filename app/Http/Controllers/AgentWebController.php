<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AgentWebController extends Controller
{
    public function loginForm()
    {
        return view('agent.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $agent = User::where('role', 'agent')->where('email', $request->email)->first();

        // Simple session auth based on existence.
        if ($agent) {
            session(['agent_id' => $agent->id, 'agent_name' => $agent->name ?? 'Agent']);
            return redirect('/agent/dashboard');
        }

        return back()->with('error', 'Agent not found.');
    }

    public function dashboard()
    {
        return view('agent.dashboard');
    }

    public function conversations()
    {
        return view('agent.conversations');
    }

    public function conversation($id)
    {
        return view('agent.conversation', compact('id'));
    }
}
