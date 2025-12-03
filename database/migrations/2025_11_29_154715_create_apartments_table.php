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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->smallInteger('area');
            $table->tinyInteger('rooms');
            $table->tinyInteger('living_rooms');
            $table->tinyInteger('bathrooms');
            $table->integer('rental_price');
            $table->string('address');
            $table->enum('status',['Booked','Free']);
            $table->decimal('average_rate',3,2)->nullable();
            $table->date('offer_date')->nullable();
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
