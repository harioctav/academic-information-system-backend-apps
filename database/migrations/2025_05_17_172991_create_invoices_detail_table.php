<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('item_name'); // e.g., Admin Fee, Shipping Fee
            $table->decimal('amount', 12, 2); // e.g., 50000.00
            $table->string('item_type')->nullable(); // Optional: e.g., admin_fee, shipping, etc
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
};
