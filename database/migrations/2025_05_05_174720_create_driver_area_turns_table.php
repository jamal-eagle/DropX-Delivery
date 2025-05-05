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
            $table->unsignedBigInteger('area_id');
            $table->integer('turn_order'); // ترتيب السائق في الدور
            $table->boolean('is_next')->default(false); // هل هو التالي في الدور
            $table->boolean('is_active')->default(true); // مفعّل أو لا
            $table->timestamp('last_assigned_at')->nullable(); // آخر مرة أخذ فيها طلب
            $table->timestamps();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
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
