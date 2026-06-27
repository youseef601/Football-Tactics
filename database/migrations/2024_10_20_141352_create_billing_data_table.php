<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_data', function (Blueprint $table) {
            $table->id(); // auto-incrementing primary key
            $table->string('apartment')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('street')->nullable();
            $table->string('building')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country', 3); // Assuming country codes (like 'EG' or 'US')
            $table->string('email');
            $table->string('floor')->nullable();
            $table->string('state')->nullable();
            $table->decimal('total_price', 10, 2); // Assuming price can have up to 2 decimal places
            $table->string('currency', 3); // e.g., 'EGP'
            $table->string('payment_methods'); // For storing array of payment methods as JSON

            // New columns for foreign keys
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_data');
    }
}
