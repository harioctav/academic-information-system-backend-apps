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
    Schema::create('login_activities', function (Blueprint $table) {
      $table->id();
      $table->string('uuid')->index()->nullable();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('ip_address');
      $table->string('user_agent');
      $table->string('location')->nullable();
      $table->string('status'); // success, failed
      $table->timestamp('login_at');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('login_activities');
  }
};
