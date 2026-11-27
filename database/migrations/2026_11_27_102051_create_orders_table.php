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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('table_number')->nullable();
            $table->enum('order_type', ['dine_in', 'takeaway']);
            $table->integer('total_price');
            $table->integer('pay_amount');
            $table->integer('change_amount');
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('status', ['processing', 'completed'])->default('processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
