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
        Schema::create('driver_commission_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('driver_percentage', 5, 2)->default(80); 
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_commission_settings');
    }
};
