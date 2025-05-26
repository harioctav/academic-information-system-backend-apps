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
            $table->uuid('uuid')->index();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('billing_id')->nullable()->constrained('billings')->onDelete('set null');

            $table->enum('payment_method', ['transfer', 'cash'])->default('transfer');
            $table->enum('payment_plan', ['cicil', 'lunas'])->default('lunas');

            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0); // tambah default

            $table->string('transfer_to')->nullable(); // e.g. BCA a.n Yayasan Pendidikan
            $table->string('proof_of_payment')->nullable(); // optional file path
            $table->enum('payment_status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
