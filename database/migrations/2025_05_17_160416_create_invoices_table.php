<?php
// database/migrations/2024_08_21_000000_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('payment_method')->nullable(); // sebelumnya: payment_system
            $table->decimal('bank_fee', 12, 2)->default(0);
            $table->decimal('subscription_fee', 12, 2)->default(0); // perbaikan dari typo: suscription fee
            $table->string('subscription_code')->nullable(); // sebelumnya: subscription id
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->string('billing_code')->nullable(); // sebelumnya: billing id
            $table->enum('payment_status', ['unpaid', 'paid', 'canceled'])->default('unpaid'); // sebelumnya: status bayar
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
