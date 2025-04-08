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
        Schema::create('image_for_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meals_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_for_meals');
    }
};
