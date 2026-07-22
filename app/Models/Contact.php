<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Contact extends Model
{
    use LogsActivity;

    protected $fillable = ['company_id', 'name', 'phone', 'email', 'designation', 'created_by'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Contacts are private to their creating agent; admins see everything.
     * Pass the acting user to scope a query to what they may see.
     */
    public function scopeVisibleTo($query, User $user)
    {
        // The `created_by` column may not exist yet if migrations haven't run on
        // this environment — guard so the Contacts page degrades (shows all)
        // instead of 500-ing. Run `php artisan migrate` to enable scoping.
        if (! $user->isAdmin() && Schema::hasColumn('contacts', 'created_by')) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
