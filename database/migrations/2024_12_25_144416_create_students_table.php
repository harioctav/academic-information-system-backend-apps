<?php

use App\Enums\Academics\StudentRegistrationStatus;
use App\Enums\GenderType;
use App\Enums\ReligionType;
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
    Schema::create('students', function (Blueprint $table) {
      $table->id();
      $table->string('uuid')->index();
      $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
      $table->string('nim')->unique();
      $table->string('nik')->unique()->nullable();
      $table->string('name');
      $table->string('email')->unique()->nullable();
      $table->date('birth_date')->nullable();
      $table->string('birth_place')->nullable();
      $table->enum('gender', array_column(GenderType::cases(), 'value'))->default(GenderType::Unknown->value);
      $table->string('phone')->unique()->nullable();
      $table->enum('religion', array_column(ReligionType::cases(), 'value'))->default(ReligionType::Unknown->value);
      $table->string('initial_registration_period')->nullable();
      $table->string('origin_department')->nullable();
      $table->string('upbjj')->nullable();
      $table->enum('status_registration', array_column(StudentRegistrationStatus::cases(), 'value'))->default(StudentRegistrationStatus::Unknown->value);
      $table->boolean('status_activity')->default(true);
      $table->longText('student_photo_path')->nullable();
      $table->string('parent_name')->nullable();
      $table->string('parent_phone_number')->unique()->nullable();
      $table->timestamps();

      // Add composite index for common queries
      $table->index(['major_id', 'status_activity']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('students');
  }
};
