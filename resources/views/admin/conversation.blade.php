@extends('layouts.app')

@section('title', 'Admin - Conversation Details')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Conversation #{{ $id }}</h2>
        <a href="/admin/conversations" class="text-gray-500 hover:text-gray-700 font-medium">&larr; Back to List</a>
    </div>

    <div class="p-4 border rounded bg-gray-50 text-gray-600">
        <p>Admin read-only view. To inspect full messages, the corresponding API endpoint needs to be called.</p>
        <p>Currently viewing ID: <strong>{{ $id }}</strong></p>
    </div>
</div>
@endsection