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
      $table->timestamp('last_activity')->after('status')->nullable();
      $table->integer('failed_login_attempts')->after('last_activity')->default(0);
      $table->timestamp('locked_until')->after('failed_login_attempts')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('last_activity');
      $table->dropColumn('failed_login_attempts');
      $table->dropColumn('locked_until');
    });
  }
};
