<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('session_category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('duration_minutes')->default(60);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->integer('default_capacity')->default(20);
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->boolean('requires_trainer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_types');
    }
};