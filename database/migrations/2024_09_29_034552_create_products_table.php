<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);  // Reduce length to 191 characters
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->json('imgs')->nullable();
            $table->integer('price'); // Ensure price is required
            $table->string('size', 50);  // Ensure size is required
            $table->integer('stock'); // Ensure stock is required
            $table->string('status')->nullable();
            $table->integer('discount'); // Ensure discount is required
            $table->integer('final_price'); // Ensure final price is required
            $table->timestamps();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('leagues_id')->nullable()->constrained('leagues')->onDelete('cascade');
            // Add unique constraint for 'name' and 'size' combination
            $table->unique(['name', 'size']);
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
