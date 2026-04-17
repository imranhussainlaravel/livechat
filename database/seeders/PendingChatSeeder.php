<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Visitor;
use Illuminate\Support\Str;

class PendingChatSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Alex Johnson', 'Sarah Parker', 'Michael Chen', 'Emma Davis', 'James Wilson'];
        $messages = [
            'How much for shipping?',
            'Where is my order #12345?',
            'Do you offer custom printing?',
            'I need help with my billing',
            'Can you help me choose a box size?'
        ];

        DB::transaction(function () use ($names, $messages) {
            foreach ($names as $index => $name) {
                $visitor = Visitor::create([
                    'name' => $name,
                    'email' => str_replace(' ', '.', strtolower($name)) . '@example.com',
                    'session_token' => Str::random(32),
                ]);

                $chatId = DB::table('chats')->insertGetId([
                    'visitor_id'        => $visitor->id,
                    'status'            => 'pending',
                    'queue_status'      => 'queued',
                    'assigned_agent_id' => null,
                    'priority'          => 'normal',
                    'subject'           => $messages[$index],
                    'started_at'        => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                DB::table('chat_messages')->insert([
                    'chat_id'     => $chatId,
                    'sender_type' => 'visitor',
                    'sender_id'   => $visitor->id,
                    'message'     => $messages[$index],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        });
    }
}
