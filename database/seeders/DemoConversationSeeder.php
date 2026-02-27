<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Visitor;
use Illuminate\Support\Str;

class DemoConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Must insert exactly 2 demo "conversations" (chats), both queued (pending), open, no agent assigned.

        $now = now();

        DB::transaction(function () use ($now) {

            // Ensure demo visitors exist using Eloquent for simplicity since they need proper names and tokens
            $visitor1 = Visitor::firstOrCreate(
                ['email' => 'visitor_demo_1@demo.com'],
                [
                    'name' => 'Demo Visitor 1',
                    'session_token' => Str::random(32),
                ]
            );

            $visitor2 = Visitor::firstOrCreate(
                ['email' => 'visitor_demo_2@demo.com'],
                [
                    'name' => 'Demo Visitor 2',
                    'session_token' => Str::random(32),
                ]
            );

            // 1. Insert Conversation (chat) 1
            $chat1Id = DB::table('chats')->insertGetId([
                'visitor_id'        => $visitor1->id,
                'status'            => 'pending', // PENDING represents the chat status
                'queue_status'      => 'queued', // Explicitly setting queue_status
                'assigned_agent_id' => null,
                'created_at'        => $now,
                'updated_at'        => $now,
                // Additional necessary defaults for our chats table schema
                'priority'          => 'normal',
                'started_at'        => $now,
            ]);

            // 2. Insert Conversation (chat) 2
            $chat2Id = DB::table('chats')->insertGetId([
                'visitor_id'        => $visitor2->id,
                'status'            => 'pending',
                'queue_status'      => 'queued',
                'assigned_agent_id' => null,
                'created_at'        => $now,
                'updated_at'        => $now,
                'priority'          => 'normal',
                'started_at'        => $now,
            ]);

            // 3. Insert Message 1
            DB::table('chat_messages')->insert([
                'chat_id'     => $chat1Id,
                'sender_type' => 'visitor',
                'sender_id'   => $visitor1->id, // Actual user/visitor ID
                'message'     => 'Hello, I need packaging quote',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // 4. Insert Message 2
            DB::table('chat_messages')->insert([
                'chat_id'     => $chat2Id,
                'sender_type' => 'visitor',
                'sender_id'   => $visitor2->id,
                'message'     => 'I want custom box prices',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        });
    }
}
