<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Visitor;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Ticket;
use App\Models\Followup;
use App\Models\Activity;
use App\Enums\UserRole;
use App\Enums\ChatStatus;
use App\Enums\QueueStatus;
use App\Enums\PriorityLevel;
use App\Enums\MessageSenderType;
use App\Enums\TicketStatus;
use App\Enums\FollowupStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---------------------------------------------------------
        // A. Agents
        // ---------------------------------------------------------
        $agent1 = User::firstOrCreate(
            ['email' => 'ali@test.com'],
            [
                'name'         => 'Ali Khan',
                'password'     => Hash::make('password'),
                'role'         => UserRole::AGENT->value,
                'status'       => 'online',
                'max_chats'    => 5,
            ]
        );

        $agent2 = User::firstOrCreate(
            ['email' => 'ahmed@test.com'],
            [
                'name'         => 'Ahmed Raza',
                'password'     => Hash::make('password'),
                'role'         => UserRole::AGENT->value,
                'status'       => 'online',
                'max_chats'    => 5,
            ]
        );

        // ---------------------------------------------------------
        // Create Visitors
        // ---------------------------------------------------------
        $visitor1 = Visitor::firstOrCreate(
            ['email' => 'visitor1001@demo.com'],
            [
                'name'          => 'Visitor 1001',
                'session_token' => Str::random(32),
            ]
        );

        $visitor2 = Visitor::firstOrCreate(
            ['email' => 'visitor1002@demo.com'],
            [
                'name'          => 'Visitor 1002',
                'session_token' => Str::random(32),
            ]
        );

        // ---------------------------------------------------------
        // B. Chat #1 (Assigned Chat)
        // ---------------------------------------------------------
        $chat1 = Chat::updateOrCreate(
            [
                'visitor_id'        => $visitor1->id,
                'assigned_agent_id' => $agent1->id,
            ],
            [
                'status'       => ChatStatus::ACTIVE,
                'queue_status' => QueueStatus::PICKED,
                'priority'     => PriorityLevel::NORMAL,
                'started_at'   => now(),
                'metadata'   => ['source' => 'website'],
                'subject'    => 'Packaging quote for boxes',
            ]
        );

        // Messages for Chat #1
        if ($chat1->messages()->count() === 0) {
            $chat1->messages()->createMany([
                [
                    'sender_type' => MessageSenderType::SYSTEM,
                    'sender_id'   => null,
                    'message'     => 'Chat started',
                ],
                [
                    'sender_type' => MessageSenderType::BOT,
                    'sender_id'   => null,
                    'message'     => 'Hello! How can I help you?',
                ],
                [
                    'sender_type' => MessageSenderType::VISITOR,
                    'sender_id'   => $visitor1->id,
                    'message'     => 'I need packaging quote for boxes',
                ],
                [
                    'sender_type' => MessageSenderType::SYSTEM,
                    'sender_id'   => null,
                    'message'     => "Agent Ali joined the chat",
                ],
                [
                    'sender_type' => MessageSenderType::AGENT,
                    'sender_id'   => $agent1->id,
                    'message'     => 'Sure, please share box size and quantity',
                ],
            ]);
        }

        // ---------------------------------------------------------
        // C. Ticket for Chat #1
        // ---------------------------------------------------------
        $ticket = Ticket::updateOrCreate(
            ['chat_id' => $chat1->id],
            [
                'agent_id'       => $agent1->id,
                'status'         => TicketStatus::INTERESTED,
                'quotation_sent' => true,
                'amount'         => 250.00,
            ]
        );

        // ---------------------------------------------------------
        // D. Follow-up for Chat #1
        // ---------------------------------------------------------
        $followup = Followup::updateOrCreate(
            ['chat_id' => $chat1->id],
            [
                'agent_id'      => $agent1->id,
                'followup_time' => now()->addDays(2),
                'status'        => FollowupStatus::PENDING,
            ]
        );

        // ---------------------------------------------------------
        // E. Chat #2 (Queue Chat)
        // ---------------------------------------------------------
        $chat2 = Chat::updateOrCreate(
            [
                'visitor_id'        => $visitor2->id,
                'assigned_agent_id' => null,
            ],
            [
                'status'       => ChatStatus::PENDING,
                'queue_status' => QueueStatus::QUEUED,
                'priority'     => PriorityLevel::NORMAL,
                'started_at'   => now(),
                'metadata'   => ['source' => 'website'],
                'subject'    => 'Custom packaging pricing',
            ]
        );

        // Messages for Chat #2
        if ($chat2->messages()->count() === 0) {
            $chat2->messages()->createMany([
                [
                    'sender_type' => MessageSenderType::SYSTEM,
                    'sender_id'   => null,
                    'message'     => 'Chat started',
                ],
                [
                    'sender_type' => MessageSenderType::BOT,
                    'sender_id'   => null,
                    'message'     => 'Hello! Please wait while we connect you to an agent.',
                ],
                [
                    'sender_type' => MessageSenderType::VISITOR,
                    'sender_id'   => $visitor2->id,
                    'message'     => 'I want custom packaging pricing',
                ],
            ]);
        }

        // ---------------------------------------------------------
        // F. Activity Logs
        // ---------------------------------------------------------
        $activities = [
            [
                'action'         => 'agent_joined',
                'reference_type' => Chat::class,
                'reference_id'   => $chat1->id,
                'user_id'        => $agent1->id,
                'metadata'       => ['message' => "Agent Ali joined chat"],
            ],
            [
                'action'         => 'quote_sent',
                'reference_type' => Ticket::class,
                'reference_id'   => $ticket->id,
                'user_id'        => $agent1->id,
                'metadata'       => ['message' => 'Quote sent'],
            ],
            [
                'action'         => 'followup_created',
                'reference_type' => Followup::class,
                'reference_id'   => $followup->id,
                'user_id'        => $agent1->id,
                'metadata'       => ['message' => 'Follow-up created'],
            ],
            [
                'action'         => 'chat_created',
                'reference_type' => Chat::class,
                'reference_id'   => $chat1->id,
                'user_id'        => null,
                'metadata'       => ['message' => 'Chat created'],
            ]
        ];

        foreach ($activities as $act) {
            Activity::firstOrCreate(
                [
                    'action'         => $act['action'],
                    'reference_type' => $act['reference_type'],
                    'reference_id'   => $act['reference_id'],
                ],
                [
                    'user_id'  => $act['user_id'],
                    'metadata' => $act['metadata'],
                ]
            );
        }
    }
}
