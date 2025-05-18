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
            $table->uuid('uuid')->index();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('billing_code')->unique();
            $table->decimal('bank_fee', 12, 2)->default(0);
            $table->decimal('salut_member_fee', 12, 2)->default(0);
            $table->decimal('semester_fee', 12, 2)->default(0);
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->string('payment_method');
            $table->string('payment_status')->default('unpaid');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
