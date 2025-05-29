<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegistrationBatch;
use Illuminate\Support\Str;

class RegistrationBatchSeeder extends Seeder
{
    public function run(): void
    {
        RegistrationBatch::create([
            'uuid'        => Str::uuid(),
            'name'        => 'Registrasi Matakuliah 2024',
            'description' => 'Registrasi Mata Kuliah Masa Reg.2024.1 / 2024 Ganjil',
            'start_date'  => now()->subDays(7),
            'end_date'    => now()->addDays(23),
            'notes'       => 'Batch awal untuk mahasiswa baru',
        ]);

        RegistrationBatch::create([
            'uuid'        => Str::uuid(),
            'name'        => 'Registrasi Matakuliah 2024',
            'description' => 'Registrasi Mata Kuliah Masa Reg.2024.2 / 2024 Genap',
            'start_date'  => now()->addDays(1),
            'end_date'    => now()->addDays(30),
            'notes'       => 'Batch lanjutan untuk mahasiswa baru',
        ]);
    }
}
