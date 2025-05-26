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
            $table->string('registration_number')->unique()->nullable();
            $table->foreignId('registration_batch_id')->constrained('registration_batches')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('shipping_address');
            $table->string('student_category'); // maba,mala,rpl
            $table->string('payment_system'); // sipas, non-sipas
            $table->string('program_type'); // spp, non-spp
            $table->boolean('tutorial_service')->default(false);
            $table->string('semester');
            $table->boolean('interested_spp')->default(false);
            $table->timestamps();
            $table->index(['student_id', 'registration_batch_id']);
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
