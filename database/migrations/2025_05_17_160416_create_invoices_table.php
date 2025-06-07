<?php

use Illuminate\Support\Facades\Schema;
use App\Enums\Finances\SettlementStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('invoices', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique()->index();
      $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
      $table->foreignId('billing_id')->constrained('billings')->onDelete('cascade');
      $table->decimal('total_amount', 12, 2)->default(0.00);
      $table->date('due_date')->nullable();
      $table->enum('payment_status', array_column(SettlementStatus::cases(), 'value'))->default(SettlementStatus::Unpaid->value);
      $table->string('payment_method')->nullable();
      $table->string('payment_type')->nullable();
      $table->text('note')->nullable();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('invoices');
  }
};
