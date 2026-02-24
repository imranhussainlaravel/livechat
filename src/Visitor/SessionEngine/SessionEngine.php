<?php

namespace Src\Visitor\SessionEngine;

use Src\Database\Models\Visitor;
use Illuminate\Support\Str;

/**
 * Session Engine — manages visitor identity and sessions.
 *
 * Visitors are identified by a persistent session token.
 * No cookies or server-side sessions — token is the identity.
 */
class SessionEngine
{
    /**
     * Find or create a visitor by session token.
     */
    public function resolve(string $sessionToken): Visitor
    {
        return Visitor::firstOrCreate(
            ['session_token' => $sessionToken],
            ['name' => 'Visitor', 'metadata' => null]
        );
    }

    /**
     * Register a visitor with profile data.
     */
    public function register(array $data): Visitor
    {
        return Visitor::create([
            'session_token' => $data['session_token'] ?? Str::uuid()->toString(),
            'name'          => $data['name'] ?? 'Visitor',
            'email'         => $data['email'] ?? null,
            'metadata'      => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Generate a new session token.
     */
    public function generateToken(): string
    {
        return Str::uuid()->toString();
    }
}
