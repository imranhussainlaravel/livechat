<?php

namespace App\Http\Controllers;

class ChatWebController extends Controller
{
    public function show($conversation_id)
    {
        return view('chat.show', compact('conversation_id'));
    }
}
