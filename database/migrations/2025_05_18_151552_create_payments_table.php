<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Billing;
use App\Models\Invoice;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Enums\Finances\PaymentPlan;
use App\Enums\Finances\PaymentMethod;
use App\Enums\Finances\PaymentStatus;

class PaymentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Payments data...');

    $faker = Factory::create('id_ID');
    $studentIds = Student::pluck('id')->toArray();
    $billingIds = Billing::pluck('id')->toArray();
    $invoiceIds = Invoice::pluck('id')->toArray();

    if (empty($studentIds)) {
      $this->command->error('No students found. Please run StudentSeeder first.');
      return;
    }

    DB::beginTransaction();

    try {
      SeederProgressBar::withProgressBar($this->command, 200, function () use ($faker, $studentIds, $billingIds, $invoiceIds) {
        $paymentMethod = $faker->randomElement(PaymentMethod::cases())->value;
        $paymentPlan = $faker->randomElement(PaymentPlan::cases())->value;
        $paymentStatus = $faker->randomElement([
          PaymentStatus::Pending->value,
          PaymentStatus::Paid->value,
          PaymentStatus::Rejected->value,
        ]);

        $amountPaid = $faker->numberBetween(500000, 3000000);

        $note = $faker->randomElement([
          'Pembayaran biaya kuliah',
          'Transfer SPP semester',
          'Cicilan biaya pendidikan',
          'Pembayaran tagihan akademik',
          'LUNAS - Biaya semester',
          'Dibatalkan oleh sistem',
          'Verifikasi menunggu konfirmasi',
          'Pembayaran melalui virtual account',
        ]);

        // Tambahan catatan berdasarkan status
        if ($paymentStatus === PaymentStatus::Paid->value) {
          $note .= ' - LUNAS';
        } elseif ($paymentStatus === PaymentStatus::Rejected->value) {
          $note .= ' - DIBATALKAN';
        }

        return collect([
          Payment::create([
            'uuid' => Str::uuid(),
            'student_id' => $faker->randomElement($studentIds),
            'billing_id' => $faker->optional()->randomElement($billingIds),
            'invoice_id' => $faker->optional()->randomElement($invoiceIds),
            'payment_method' => $paymentMethod,
            'payment_plan' => $paymentPlan,
            'payment_date' => $faker->optional(0.85)->dateTimeBetween('-1 years', 'now')->format('Y-m-d'),
            'amount_paid' => $amountPaid,
            'transfer_to' => $faker->randomElement([
              'BCA a.n Yayasan Pendidikan',
              'Mandiri a.n Universitas Contoh',
              'BNI a.n Kampus ABC',
              null,
            ]),
            'proof_of_payment' => $faker->optional(0.5)->imageUrl(640, 480, 'business', true, 'payment'),
            'payment_status' => $paymentStatus,
            'note' => $note,
            'created_at' => $faker->dateTimeBetween('-1 years', '-1 months'),
            'updated_at' => now(),
          ])
        ]);
      });

      DB::commit();
      $this->command->info('Payments created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error('Error creating payments: ' . $e->getMessage());
      throw $e;
    }
  }
}
