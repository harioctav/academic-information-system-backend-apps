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
    Schema::create('subjects', function (Blueprint $table) {
      $table->id();
      $table->string('uuid');
      $table->string('code');
      $table->string('name');
      $table->string('course_credit');
      $table->string('subject_status');
      $table->string('exam_time')->nullable();
      $table->text('note')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subjects');
  }
};
