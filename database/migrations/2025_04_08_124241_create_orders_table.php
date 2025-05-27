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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['pending', 'preparing', 'on_delivery', 'delivered', 'rejected']);
            $table->boolean('is_accepted')->default(false);
            $table->decimal('total_price', 10, 2);
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(10000);
            $table->text('barcode')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
