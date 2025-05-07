<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_area_turns', function (Blueprint $table) {
            $table->timestamp('turn_assigned_at')->nullable(); // متى تم تعيين الدور لهذا السائق
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_area_turns', function (Blueprint $table) {
            //
        });
    }
};
