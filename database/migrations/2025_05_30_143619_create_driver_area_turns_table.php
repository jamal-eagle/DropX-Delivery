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
        Schema::create('driver_area_turns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->integer('turn_order');
            $table->boolean('is_next')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('turn_assigned_at')->nullable();
            $table->timestamp('last_assigned_at')->nullable();
            $table->timestamps();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_area_turns');
    }
};
