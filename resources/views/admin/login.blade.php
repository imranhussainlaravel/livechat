@extends('layouts.app')

@section('title', 'Admin Login - LiveChat')

@section('content')
<div class="flex justify-center mt-12">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Admin Login</h2>

        <form method="POST" action="/admin/login">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-indigo-300" placeholder="admin@example.com" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-indigo-300" placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 transition">
                Secure Login
            </button>
        </form>
    </div>
</div>
@endsection