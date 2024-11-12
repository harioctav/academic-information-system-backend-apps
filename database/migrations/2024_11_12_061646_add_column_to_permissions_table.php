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
    Schema::table('permissions', function (Blueprint $table) {
      $table->char('uuid')->index()->after('id');
      $table->foreignId('permission_category_id')->after('uuid')->constrained()->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('permissions', function (Blueprint $table) {
      $table->dropForeign('permissions_permission_category_id_foreign');
      $table->dropColumn('uuid');
      $table->dropColumn('permission_category_id');
    });
  }
};
