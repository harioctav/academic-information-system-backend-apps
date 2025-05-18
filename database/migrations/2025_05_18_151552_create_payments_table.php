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
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->onDelete('set null');
            $table->foreignId('billing_id')->nullable()->constrained('billings')->onDelete('set null');
            $table->string('payment_type'); // e.g. tuition, spp, etc.
            $table->string('payment_method'); // e.g. bank transfer, cash
            $table->string('payment_status')->default('pending');
            $table->date('payment_date');
            $table->decimal('amount_paid', 12, 2);
            $table->string('proof_of_payment')->nullable(); // path to file
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
