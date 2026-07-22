<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('industry_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('designation')->nullable();
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('status')->default('new');
            $table->string('product_interest');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reassigned_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reassigned_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_note')->nullable();
            // Kanban ordering column (drag-drop board comes in a later pass).
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->unique(['status', 'position']);
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('material')->nullable();
            $table->string('size_options')->nullable();
            $table->unsignedInteger('moq')->default(1);
            $table->decimal('base_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sales_rep_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage')->default('quoted');
            $table->decimal('value', 12, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->unsignedTinyInteger('probability')->nullable();
            $table->string('lost_reason')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();

            $table->unique(['stage', 'position']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->date('deadline')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->decimal('total_value', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('discount_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('discount_approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('vehicle_info')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('invoice_no')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('event');
            $table->string('description');
            $table->timestamp('created_at')->nullable();
            $table->index(['subject_type', 'subject_id']);
        });

        // Discount-approval threshold used by Quotation::needsDiscountApproval().
        Schema::table('system_settings', function (Blueprint $table) {
            $table->decimal('discount_approval_threshold', 5, 2)->default(10)->after('widget_name');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn('discount_approval_threshold');
        });

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('dispatches');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('product_price_tiers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('companies');
    }
};
