<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'city', 'industry_notes'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function leads(): HasManyThrough
    {
        return $this->hasManyThrough(Lead::class, Contact::class);
    }
}
