<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('session_slot_id')
                ->nullable()
                ->after('class_schedule_id')
                ->constrained('session_slots')
                ->nullOnDelete();

            $table->index('session_slot_id', 'bookings_session_slot_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_session_slot_id_idx');
            $table->dropForeign(['session_slot_id']);
            $table->dropColumn('session_slot_id');
        });
    }
};
