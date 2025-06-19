<?php

use App\Enums\Finances\PaymentPlan;
use App\Enums\Finances\PaymentMethod;
use App\Enums\Finances\PaymentStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('billing_id')->nullable()->constrained('billings')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('payment_code')->unique();
            $table->enum(
                'payment_method',
                array_column(PaymentMethod::cases(), 'value')
            )->default(PaymentMethod::Transfer->value);
            $table->enum(
                'payment_plan',
                array_column(PaymentPlan::cases(), 'value')
            )->default(PaymentPlan::Lunas->value);

            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0); // tambah default

            $table->string('transfer_to')->nullable(); // e.g. BCA a.n Yayasan Pendidikan
            $table->string('proof_of_payment')->nullable(); // optional file path
            $table->enum('payment_status', array_column(PaymentStatus::cases(), 'value'))->default(PaymentStatus::Pending->value);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
