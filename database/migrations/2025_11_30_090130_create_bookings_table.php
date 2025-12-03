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
        $statuses = [
            'pending', // waiting for the owner to confirme the booking request
            'confirmed', // owner confirm client request
            'rejected', // owner reject client request
            'cancelled', // client cancel his/her request
            'completed', // client checked out and the booking completed
            'expired', // owner does not confirm client request and the time is up
            'payment_pending', // waiting for the client to pay
            'payment_failed', // something in payment went wrong
            'no_show', // client pay and haven't checked in
            'checked_in', // client arraived to appartment
            'modified', // client modifie his request
        ];

        Schema::create('bookings', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('apartment_id')->constrained('apartments');
            $table->date('check_in_date')->check('check_in_date >= CURRENT_DATE');
            $table->date('check_out_date')->check('check_out_date >= check_in_date');
            $table->enum('payment_method', ['credit', 'bank_transfer', 'cash', 'digital_wallet']);
            $table->enum('booking_status', $statuses)->default('pending');
            $table->tinyInteger('rate')->check('rate >= 1 AND rate <= 10')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
