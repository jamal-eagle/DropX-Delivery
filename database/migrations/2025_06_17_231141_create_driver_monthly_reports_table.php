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
        Schema::create('driver_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->date('month_date');
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_delivery_fees', 10, 2)->default(0);
            $table->decimal('driver_earnings', 10, 2)->default(0);
            $table->decimal('admin_earnings', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['driver_id', 'month_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_monthly_reports');
    }
};
