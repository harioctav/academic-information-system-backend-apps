<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index(); // atau bisa ->unique() jika tidak ingin index terpisah
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->string('registration_period')->nullable();
            $table->string('billing_code')->unique();
            $table->enum('billing_status', ['active', 'non-active'])->nullable();
            $table->decimal('bank_fee', 12, 2)->default(0);
            $table->decimal('salut_member_fee', 12, 2)->default(0);
            $table->decimal('semester_fee', 12, 2)->default(0);
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->enum('settlement_status', ['unpaid', 'paid', 'canceled'])->default('unpaid');
            $table->date('settlement_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
