<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(20);
            $table->decimal('price', 12, 2)->nullable();       // null = use session type's base_price
            $table->string('location', 100)->nullable();
            $table->enum('status', ['open', 'full', 'cancelled', 'completed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['slot_date', 'status']);
            $table->index(['session_type_id', 'slot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_slots');
    }
};