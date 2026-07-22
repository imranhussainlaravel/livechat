<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WorkScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'max_chats',
        'last_seen_at',
        // CRM fields
        'work_scope',
        'account_status',
        'created_by_admin_id',
        'can_live_chat',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            // CRM casts. NOTE: `status` stays LiveChat presence (online/away/busy/offline);
            // the CRM account active/inactive concept lives on `account_status`.
            'work_scope' => WorkScope::class,
            'account_status' => UserStatus::class,
            'can_live_chat' => 'boolean',
        ];
    }

    /** Whether this user may access the Live Chat side (admins always can). */
    public function canLiveChat(): bool
    {
        return $this->isAdmin() || $this->can_live_chat !== false;
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships */
    /* ------------------------------------------------------------------ */

    public function assignedChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'assigned_agent_id');
    }

    public function sentInternalMessages(): HasMany
    {
        return $this->hasMany(InternalMessage::class, 'sender_id');
    }

    public function receivedInternalMessages(): HasMany
    {
        return $this->hasMany(InternalMessage::class, 'receiver_id');
    }

    /* ------------------------------------------------------------------ */
    /*  CRM relationships */
    /* ------------------------------------------------------------------ */

    /** Leads assigned to this user (as agent). */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_agent_id');
    }

    /** Deals this user owns (as sales rep). */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'sales_rep_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers */
    /* ------------------------------------------------------------------ */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::AGENT;
    }

    public function isProduction(): bool
    {
        return $this->role === UserRole::PRODUCTION;
    }

    /** CRM account enabled (distinct from live-chat presence). */
    public function isActive(): bool
    {
        return $this->account_status !== UserStatus::Inactive;
    }

    /** Whether this user may create CRM leads (admins always; agents gated by work_scope). */
    public function canCreateLeads(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->work_scope?->canCreateLeads() ?? true;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'online'
            && $this->assignedChats()
                ->whereIn('status', ['assigned', 'active', 'transferred'])
                ->count() < $this->max_chats;
    }
}
