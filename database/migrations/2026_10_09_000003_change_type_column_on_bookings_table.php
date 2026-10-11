<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert the fixed ENUM into a plain string so booking types can be
        // managed from the `booking_types` table. Existing values are valid
        // strings, so no data is lost.
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('type', 50)->default('session')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('type', ['personal_training', 'group_class', 'session', 'consultation'])
                ->default('session')->change();
        });
    }
};
