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
    Schema::create('provinces', function (Blueprint $table) {
      $table->id();
      $table->char('uuid')->index();
      $table->string('name');
      $table->string('code');
      $table->timestamps();
    });

    Schema::create('regencies', function (Blueprint $table) {
      $table->id();
      $table->char('uuid')->index();
      $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
      $table->string('type');
      $table->string('name');
      $table->string('code');
      $table->string('full_code');
      $table->timestamps();
    });

    Schema::create('districts', function (Blueprint $table) {
      $table->id();
      $table->char('uuid')->index();
      $table->foreignId('regency_id')->constrained('regencies')->onDelete('cascade');
      $table->string('code');
      $table->string('name');
      $table->string('full_code');
      $table->timestamps();
    });

    Schema::create('villages', function (Blueprint $table) {
      $table->id();
      $table->char('uuid')->index();
      $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
      $table->string('name');
      $table->string('code');
      $table->string('full_code');
      $table->string('pos_code');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('provinces');
    Schema::dropIfExists('regencies');
    Schema::dropIfExists('districts');
    Schema::dropIfExists('villages');
  }
};
