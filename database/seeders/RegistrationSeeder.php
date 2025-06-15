<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Registration;
use App\Models\RegistrationBatch;
use App\Models\Student;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

// Import semua Enums yang sudah Anda buat
use App\Enums\Finances\PaymentSystem;
use App\Enums\Finances\ProgramType;
use App\Enums\Finances\RegistrationStatus;
use App\Enums\Finances\StudentCategory;
use App\Enums\Finances\TutorialService;


class RegistrationSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$this->command->warn(PHP_EOL . 'Creating Registrations data...');

		$faker = Factory::create('id_ID');

		$batchIds = RegistrationBatch::pluck('id')->toArray();
		$students = Student::with(['domicileAddress.village.district.regency.province'])->get();

		if (empty($batchIds)) {
			$this->command->error('No registration batches found. Please run RegistrationBatchSeeder first.');
			return;
		}

		if ($students->isEmpty()) {
			$this->command->error('No students found. Please run StudentSeeder first.');
			return;
		}

		DB::beginTransaction();

		try {
			SeederProgressBar::withProgressBar($this->command, min(150, $students->count()), function () use ($batchIds, $students, $faker) {
				$student = $students->random();

				// Build shipping address from student's domicile address
				$address = '';
				if ($student->domicileAddress) {
					$addressParts = [];

					if ($student->domicileAddress->address) {
						$addressParts[] = $student->domicileAddress->address;
					}
					if ($student->domicileAddress->village) {
						$addressParts[] = $student->domicileAddress->village->name;
					}
					if ($student->domicileAddress->village && $student->domicileAddress->village->district) {
						$addressParts[] = $student->domicileAddress->village->district->name;
					}
					if ($student->domicileAddress->village && $student->domicileAddress->village->district && $student->domicileAddress->village->district->regency) {
						$addressParts[] = $student->domicileAddress->village->district->regency->name;
					}
					if ($student->domicileAddress->village && $student->domicileAddress->village->district && $student->domicileAddress->village->district->regency && $student->domicileAddress->village->district->regency->province) {
						$addressParts[] = $student->domicileAddress->village->district->regency->province->name;
					}


					$address = implode(', ', array_filter($addressParts));
				}

				$year = $faker->numberBetween(2020, 2024);
				// Pastikan sequence unik hanya dalam konteks faker instance saat ini
				// Untuk produksi, nomor registrasi biasanya dihasilkan di model atau service
				$sequence = $faker->randomNumber(4, true); // True untuk fixed-width, agar tidak menghasilkan angka terlalu kecil
				$registrationNumber = 'REG-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

				// --- PENGGUNAAN ENUM DI SINI ---
				// Mendapatkan semua nilai case dari Enum
				$studentCategoryCases = StudentCategory::cases();
				$paymentSystemCases = PaymentSystem::cases();
				$programTypeCases = ProgramType::cases();
				$tutorialServiceCases = TutorialService::cases();

				$registration = Registration::create([
					'uuid' => Str::uuid(),
					'registration_batch_id' => $faker->randomElement($batchIds),
					'registration_number' => $registrationNumber,
					'student_id' => $student->id,
					'shipping_address' => $address ?: $faker->address(),
					// Ambil nilai random dari Enum
					'student_category' => $faker->randomElement($studentCategoryCases)->value,
					'payment_system' => $faker->randomElement($paymentSystemCases)->value,
					'program_type' => $faker->randomElement($programTypeCases)->value,
					'tutorial_service' => $faker->randomElement($tutorialServiceCases)->value, // Menggunakan Enum TutorialService
					'semester' => $faker->numberBetween(1, 8),
					'interested_spp' => $faker->boolean(80), // 80% chance true
					'registration_status' => RegistrationStatus::Pending->value, // Menggunakan Enum RegistrationStatus
					'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
					'updated_at' => now(),
				]);

				return collect([$registration]);
			});

			DB::commit();

			$this->command->info('Registrations created successfully!');
		} catch (\Exception $e) {
			DB::rollBack();
			$this->command->error('Error creating registrations: ' . $e->getMessage());
			throw $e;
		}
	}
}
