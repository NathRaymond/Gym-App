<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membership Plans
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('duration_days');
            $table->enum('duration_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');
            $table->integer('sessions_included')->nullable()->comment('null = unlimited');
            $table->integer('freeze_days')->default(0);
            $table->boolean('has_pt_sessions')->default(false);
            $table->integer('pt_sessions_count')->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Membership Subscriptions
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('membership_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled', 'frozen'])->default('pending');
            $table->integer('sessions_used')->default(0);
            $table->integer('sessions_remaining')->nullable();
            $table->integer('frozen_days_used')->default(0);
            $table->date('frozen_until')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'end_date'], 'memberships_status_end_date_idx');
        });

        // Gym Classes
        Schema::create('gym_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('image', 191)->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users');
            $table->integer('capacity')->default(20);
            $table->integer('duration_minutes')->default(60);
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced', 'all_levels'])->default('all_levels');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_free_for_members')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Class Schedules
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('users');
            $table->enum('day_of_week', ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(20);
            $table->string('room', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Trainers
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->json('specializations')->nullable();
            $table->json('certifications')->nullable();
            $table->integer('years_experience')->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_available')->default(true);
            $table->json('availability')->nullable();
            $table->timestamps();
        });

        // Bookings (Sessions / PT)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers');
            $table->foreignId('gym_class_id')->nullable()->constrained();
            $table->foreignId('class_schedule_id')->nullable()->constrained();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['personal_training', 'group_class', 'session', 'consultation'])->default('session');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->enum('payment_method', ['wallet', 'paystack', 'flutterwave', 'cash', 'pos', 'transfer', 'bank_transfer'])->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_date', 'status'], 'bookings_date_status_idx');
            $table->index(['user_id', 'status'], 'bookings_user_status_idx');
        });

        // Wallets
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('total_funded', 14, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Wallet Transactions
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('description', 255);
            $table->string('source', 100)->nullable();
            $table->string('transactionable_type', 191)->nullable();
            $table->unsignedBigInteger('transactionable_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Short custom index name
            $table->index(['transactionable_type', 'transactionable_id'], 'wtx_morph_idx');
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code', 50)->unique();
            $table->foreignId('user_id')->constrained();
            $table->string('payable_type', 191)->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('NGN');
            $table->enum('method', ['wallet', 'paystack', 'flutterwave', 'cash', 'pos', 'bank_transfer', 'manual']);
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('gateway_reference', 150)->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'payments_user_status_idx');
            $table->index('paid_at', 'payments_paid_at_idx');
            // Short custom morph index
            $table->index(['payable_type', 'payable_id'], 'payments_payable_idx');
        });

        // Tickets (for check-in)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 50)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('booking_id')->nullable()->constrained();
            $table->foreignId('membership_id')->nullable()->constrained();
            $table->string('qr_code_path', 191)->nullable();
            $table->enum('type', ['booking', 'membership', 'day_pass']);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['ticket_code', 'status'], 'tickets_code_status_idx');
        });

        // Feedback / Reviews
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feedbackable_type', 191)->nullable();
            $table->unsignedBigInteger('feedbackable_id')->nullable();
            $table->integer('rating')->default(5);
            $table->string('title', 150)->nullable();
            $table->text('message');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Short custom morph index
            $table->index(['feedbackable_type', 'feedbackable_id'], 'feedback_morph_idx');
        });

        // Notifications
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 191);
            $table->text('message');
            $table->string('type', 50)->default('info');
            $table->string('action_url', 191)->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('type', 50)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Equipment / Inventory (bonus feature)
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('condition', 50)->default('good');
            $table->date('purchased_at')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'out_of_order'])->default('available');
            $table->timestamps();
        });

        // Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content');
            $table->enum('audience', ['all', 'members', 'trainers', 'staff'])->default('all');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('trainers');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('gym_classes');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_plans');
    }
};