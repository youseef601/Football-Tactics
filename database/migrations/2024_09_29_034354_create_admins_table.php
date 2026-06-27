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
        Schema::create('admins', function (Blueprint $table) {
            $table->id(); // primary key
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // Create foreign key
            $table->string('admin_name');
            $table->timestamps(); // Adds created_at and updated_at
        });

        // Adding a unique constraint on the admin_id column after defining it
        Schema::table('admins', function (Blueprint $table) {
            $table->unique('admin_id'); // Ensure unique admin_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['admin_id']); // Drop unique constraint
        });

        Schema::dropIfExists('admins');
    }
};
