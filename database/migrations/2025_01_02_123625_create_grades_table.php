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
    Schema::create('grades', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->foreignId('student_id')->index()->constrained('students')->onDelete('cascade');
      $table->foreignId('subject_id')->index()->constrained('subjects')->onDelete('cascade');
      $table->string('grade', 5);
      $table->decimal('quality', 5, 2)->nullable();
      $table->decimal('mutu', 5, 2)->nullable();
      $table->string('exam_period', 50);
      $table->text('grade_note')->nullable();
      $table->timestamps();

      // Add composite index for common queries
      $table->index(['student_id', 'subject_id']);

      // Mencegah duplikasi nilai untuk mata kuliah yang sama
      $table->unique(['student_id', 'subject_id', 'exam_period']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('grades');
  }
};
