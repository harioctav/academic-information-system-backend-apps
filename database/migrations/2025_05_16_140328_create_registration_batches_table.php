<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('registration_batches', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->string('name');
      $table->text('description')->nullable();
      $table->date('start_date');
      $table->date('end_date');
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('registration_batches');
  }
};


// php artisan migrate --path=database/migrations/2025_05_16_140328_create_registration_batches_table.php
// php artisan migrate --path=database/migrations/2025_05_16_140329_create_registrations_table.php
