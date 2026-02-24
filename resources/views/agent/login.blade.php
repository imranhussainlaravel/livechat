@extends('layouts.app')

@section('title', 'Agent Login - LiveChat')

@section('content')
<div class="flex justify-center mt-12">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Agent Login</h2>

        <form method="POST" action="/agent/login">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Agent Email</label>
                <input type="email" name="email" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-green-300" placeholder="agent@example.com" required>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 transition">
                Enter Workplace
            </button>
        </form>
    </div>
</div>
@endsection