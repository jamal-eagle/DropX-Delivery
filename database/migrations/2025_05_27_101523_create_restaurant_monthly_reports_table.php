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
        Schema::create('restaurant_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->date('month_date');
            $table->unsignedInteger('total_orders');
            $table->decimal('total_amount', 10, 2);
            $table->enum('commission_type', ['percentage', 'fixed']);
            $table->decimal('commission_value', 10, 2);
            $table->decimal('system_earnings', 10, 2);
            $table->decimal('restaurant_earnings', 10, 2);
            $table->timestamps();
            $table->unique(['restaurant_id', 'month_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_monthly_reports');
    }
};
