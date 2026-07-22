<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('followups');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('activities');
    }

    public function down(): void
    {
        // Legacy features intentionally removed; not restored on rollback.
    }
};
