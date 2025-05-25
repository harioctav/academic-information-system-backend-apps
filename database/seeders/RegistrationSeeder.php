<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registration;
use App\Models\RegistrationBatch;
use App\Models\Student;
use Illuminate\Support\Str;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $batch = RegistrationBatch::first();
        $students = Student::with('addresses')->take(3)->get();

        foreach ($students as $i => $student) {
            $address = ($student->province?->name ?? '') . ' ' .
                ($student->regency?->name ?? '') . ' ' .
                ($student->district?->name ?? '') . ' ' .
                ($student->village?->name ?? '') . ' ' .
                (optional($student->domicileAddress)->address ?? '');

            Registration::create([
                'uuid'                  => Str::uuid(),
                'registration_batch_id' => $batch->id,
                'registration_number'   => 'REG-2024-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'student_id'            => $student->id,
                'shipping_address'      => $address ?? null,
                'student_category'      => 'new_member',
                'payment_method'        => 'sipas',
                'program_type'          => 'spp',
                'tutorial_service'      => true,
                'semester'              => '1',
                'interested_spp'        => true,
            ]);
        }
    }
}
