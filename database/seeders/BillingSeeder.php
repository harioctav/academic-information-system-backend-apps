<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Billing;
use App\Models\Student;
use App\Models\Registration;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Billings data...');

    $faker = Factory::create('id_ID');

    $studentIds = Student::pluck('id')->toArray();
    $registrationIds = Registration::pluck('id')->toArray();

    if (empty($studentIds)) {
      $this->command->error('No students found. Please run StudentSeeder first.');
      return;
    }

    if (empty($registrationIds)) {
      $this->command->error('No registrations found. Please run RegistrationSeeder first.');
      return;
    }

    DB::beginTransaction();

    try {
      SeederProgressBar::withProgressBar($this->command, 200, function () use ($studentIds, $registrationIds, $faker) {
        // Generate random registration period
        $registrationYear = $faker->numberBetween(2020, 2024);
        $registrationPeriod = $faker->randomElement(['GANJIL', 'GENAP']);
        $registrationPeriodFormatted = $registrationYear . ' ' . $registrationPeriod;

        // Generate unique billing code
        $periodCode = $registrationPeriod === 'GANJIL' ? 'G' : 'E';
        $sequence = $faker->unique()->numberBetween(1, 99999);
        $billingCode = 'BILL-' . $registrationYear . $periodCode . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Random fees
        $bankFee = $faker->randomElement([5000, 7500, 10000, 12500, 15000]);
        $salutMemberFee = $faker->randomElement([10000, 15000, 20000, 25000]);

        // Semester fee based on year
        $baseSemesterFee = [];
        switch ($registrationYear) {
          case 2020:
            $baseSemesterFee = [2000000, 2200000, 2400000];
            break;
          case 2021:
            $baseSemesterFee = [2100000, 2300000, 2500000];
            break;
          case 2022:
            $baseSemesterFee = [2200000, 2400000, 2600000];
            break;
          case 2023:
            $baseSemesterFee = [2300000, 2500000, 2700000];
            break;
          case 2024:
            $baseSemesterFee = [2400000, 2600000, 2800000];
            break;
          default:
            $baseSemesterFee = [2500000, 2700000, 2900000];
            break;
        }

        $semesterFee = $faker->randomElement($baseSemesterFee);
        $totalFee = $bankFee + $salutMemberFee + $semesterFee;

        // Status sesuai dengan enum di migration
        $billingStatus = $faker->randomElement([
          'active',
          'active',
          'active',
          'active', // 80% active
          'non-active' // 20% non-active
        ]);

        $settlementStatus = $faker->randomElement([
          'unpaid',
          'unpaid',
          'unpaid', // 30% unpaid
          'paid',
          'paid',
          'paid',
          'paid',
          'paid', // 50% paid
          'canceled',
          'canceled' // 20% canceled
        ]);

        // Generate settlement_date jika status paid
        $settlementDate = null;
        if ($settlementStatus === 'paid') {
          $settlementDate = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
        }

        // Generate note
        $periodLower = strtolower($registrationPeriod);
        $notes = [
          'Tagihan semester ' . $periodLower . ' tahun ' . $registrationYear,
          'Pembayaran SPP semester ' . $periodLower . ' TA ' . $registrationYear,
          'Tagihan kuliah periode ' . $registrationYear . ' ' . $periodLower,
          'Biaya pendidikan semester ' . $periodLower . ' ' . $registrationYear,
          'Tagihan akademik ' . $registrationYear . ' ' . $periodLower,
          'SPP dan biaya administrasi semester ' . $periodLower,
          'Tagihan kuliah reguler ' . $registrationYear . ' ' . $periodLower
        ];

        // Add special notes for certain statuses
        if ($settlementStatus === 'canceled') {
          $notes[] = 'DIBATALKAN - Tagihan semester ' . $periodLower . ' ' . $registrationYear;
          $notes[] = 'Tagihan dibatalkan karena mahasiswa mengundurkan diri';
        } elseif ($settlementStatus === 'paid') {
          $notes[] = 'LUNAS - Pembayaran semester ' . $periodLower . ' ' . $registrationYear;
        } elseif ($settlementStatus === 'unpaid') {
          $notes[] = 'BELUM LUNAS - Tagihan semester ' . $periodLower . ' ' . $registrationYear;
        }

        $billing = Billing::create([
          'uuid' => Str::uuid(),
          'billing_code' => $billingCode,
          'student_id' => $faker->randomElement($studentIds),
          'registration_id' => $faker->randomElement($registrationIds),
          'registration_period' => $registrationPeriodFormatted,
          'bank_fee' => $bankFee,
          'salut_member_fee' => $salutMemberFee,
          'semester_fee' => $semesterFee,
          'total_fee' => $totalFee,
          'billing_status' => $billingStatus,
          'settlement_status' => $settlementStatus,
          'settlement_date' => $settlementDate,
          'note' => $faker->randomElement($notes),
          'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
          'updated_at' => now(),
        ]);

        return collect([$billing]);
      });

      DB::commit();

      $this->command->info('Billings created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error('Error creating billings: ' . $e->getMessage());
      throw $e;
    }
  }
}
