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
    Schema::table('users', function (Blueprint $table) {
      $table->char('uuid')->index()->after('id');
      $table->string('photo_profile_path')->nullable()->after('password');
      $table->boolean('status')->default(true)->after('photo_profile_path');
      $table->string('phone', 50)->nullable()->unique()->after('email');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('uuid');
      $table->dropColumn('photo_profile_path');
      $table->dropColumn('phone');
      $table->dropColumn('status');
    });
  }
};
