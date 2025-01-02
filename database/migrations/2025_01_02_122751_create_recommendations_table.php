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
    Schema::create('recommendations', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->foreignId('student_id')->index()->constrained('students')->onDelete('cascade');
      $table->foreignId('subject_id')->index()->constrained('subjects')->onDelete('cascade');
      $table->tinyInteger('semester')->unsigned();
      $table->string('exam_period', 50)->nullable();
      $table->text('note')->nullable();
      $table->timestamps();

      // Add composite index for common queries
      $table->index(['student_id', 'subject_id', 'semester']);

      // Mencegah duplikasi rekomendasi untuk mata kuliah yang sama
      $table->unique(['student_id', 'subject_id', 'semester']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('recommendations');
  }
};
