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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();

            // Relasi ke tabel students
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // Relasi ke tabel student_addresses (bisa pilih salah satu tipe: domicile / id_card)
            $table->foreignId('address_id')->constrained('student_addresses')->onDelete('cascade');

            $table->string('student_category'); // e.g., returning_member / new_member / rpl
            $table->string('payment_method'); // e.g., sipas / non_sipas
            $table->string('program_type'); // e.g., spp / non_spp
            $table->boolean('tutorial_service')->default(false);
            $table->string('semester'); // e.g., 2024-1
            $table->boolean('interested_spp')->default(false);

            $table->timestamps();

            // Optional indexing for optimization
            $table->index(['student_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
