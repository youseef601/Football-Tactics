<?php

// Migration for admin_work table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_work', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade'); // Foreign key
            $table->string('slide1')->nullable(); // Image path for Slide 1
            $table->string('slide2')->nullable(); // Image path for Slide 2
            $table->string('slide3')->nullable(); // Image path for Slide 3
            $table->string('text1')->nullable();
            $table->string('text2')->nullable();
            $table->string('text3')->nullable();
            $table->foreignId('best1')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('best2')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('best3')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('best4')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('new1')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('new2')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('new3')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('new4')->nullable()->constrained('products')->onDelete('cascade');
            $table->timestamps(); // Timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_work');
    }
};
