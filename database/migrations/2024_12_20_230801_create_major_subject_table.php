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
    Schema::create('major_has_subjects', function (Blueprint $table) {
      $table->id();
      $table->string('uuid')->index();
      $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
      $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
      $table->integer('semester');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('major_has_subjects');
  }
};
