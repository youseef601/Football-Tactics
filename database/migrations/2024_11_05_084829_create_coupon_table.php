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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon')->unique(); // Unique coupon code
            $table->string('discount', 10); // Discount value, allowing words or numbers with one decimal
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade'); // Foreign key to admins table
            $table->boolean('used')->default(false); // Add a 'used' column with a default value of false
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
