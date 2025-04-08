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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->unique();
            $table->string('password');
            $table->enum('user_type', ['customer', 'restaurant', 'driver', 'admin'])->default('customer');
            $table->boolean('is_active')->default(false);
            $table->string('profile_image', 255)->nullable();
            $table->text('fcm_token')->nullable();
            $table->softDeletes(); // deleted_at
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
