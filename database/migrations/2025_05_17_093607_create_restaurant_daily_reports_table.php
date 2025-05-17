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
        Schema::create('restaurant_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('total_orders');
            $table->decimal('total_amount', 10, 2);
            $table->enum('commission_type', ['percentage', 'fixed']);
            $table->decimal('commission_value', 10, 2);
            $table->decimal('system_earnings', 10, 2);
            $table->decimal('restaurant_earnings', 10, 2); // الحقل الجديد الذي طلبته
            $table->timestamps();
            $table->unique(['restaurant_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_daily_reports');
    }
};
