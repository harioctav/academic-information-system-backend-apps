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
    Schema::create('majors', function (Blueprint $table) {
      $table->id();
      $table->string('uuid');
      $table->string('code')->unique();
      $table->string('name')->unique();
      $table->string('degree');
      $table->integer('total_course_credit')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('majors');
  }
};
