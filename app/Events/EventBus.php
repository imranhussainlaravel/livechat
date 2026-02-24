<?php

namespace App\Events;

use Illuminate\Support\Facades\Event;

/**
 * Event Bus — central event dispatcher.
 *
 * Maps domain events to their handlers. Uses Laravel's native
 * event system under the hood but provides a typed domain API.
 */
class EventBus
{
    /**
     * Dispatch a domain event.
     */
    public function dispatch(string $eventClass, mixed ...$args): void
    {
        Event::dispatch(new $eventClass(...$args));
    }

    /**
     * Register a listener for a domain event.
     */
    public function listen(string $eventClass, string|callable $listener): void
    {
        Event::listen($eventClass, $listener);
    }
}
