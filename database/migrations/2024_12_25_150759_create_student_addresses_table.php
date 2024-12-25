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
    Schema::create('student_addresses', function (Blueprint $table) {
      $table->id();
      $table->string('uuid');
      $table->foreignId('student_id')->constrained()->onDelete('cascade');
      $table->foreignId('village_id')->nullable()->constrained('villages')->onDelete('cascade');
      $table->enum('type', ['domicile', 'id_card']);
      $table->text('address');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('student_addresses');
  }
};
