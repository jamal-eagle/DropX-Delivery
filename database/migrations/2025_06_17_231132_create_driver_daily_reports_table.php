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
        Schema::create('driver_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('driver_earnings', 10, 2)->default(0);
            $table->decimal('admin_earnings', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['driver_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_daily_reports');
    }
};
