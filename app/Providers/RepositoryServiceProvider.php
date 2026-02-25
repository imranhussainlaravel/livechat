<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\FollowupRepositoryInterface;
use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use App\Repositories\FollowupRepository;
use App\Repositories\TicketRepository;
use App\Repositories\ActivityRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     */
    public array $bindings = [
        UserRepositoryInterface::class     => UserRepository::class,
        ChatRepositoryInterface::class     => ChatRepository::class,
        MessageRepositoryInterface::class  => MessageRepository::class,
        FollowupRepositoryInterface::class => FollowupRepository::class,
        TicketRepositoryInterface::class   => TicketRepository::class,
        ActivityRepositoryInterface::class => ActivityRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
