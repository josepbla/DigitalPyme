<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('diagnostic_request_id')->constrained()->restrictOnDelete();
            $table->string('return_token', 36)->nullable()->unique();
            $table->string('mercadopago_preference_id')->nullable()->unique();
            $table->string('mercadopago_payment_id')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};