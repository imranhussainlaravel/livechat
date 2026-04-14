<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatRepositoryInterface $chats) {}

    public function index(Request $request)
    {
        $chats = $this->chats->getByStatus(
            $request->get('status', 'pending'),
            $request->get('per_page', 15),
        );

        return view('admin.chats.index', compact('chats'));
    }
}
