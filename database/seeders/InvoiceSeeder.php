<?php

namespace Database\Seeders;

use App\Enums\Finances\SettlementStatus;
use App\Helpers\SeederProgressBar;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Student;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn(PHP_EOL . 'Creating Invoices data...');

        $faker = Factory::create('id_ID');

        $studentIds = Student::pluck('id')->toArray();
        $billingIds = Billing::pluck('id')->toArray();

        if (empty($studentIds)) {
            $this->command->error('No students found. Please run StudentSeeder first.');
            return;
        }

        if (empty($billingIds)) {
            $this->command->error('No billings found. Please run BillingSeeder first.');
            return;
        }

        DB::beginTransaction();

        try {
            SeederProgressBar::withProgressBar($this->command, 200, function () use ($faker, $studentIds, $billingIds) {
                $studentId = $faker->randomElement($studentIds);
                $billingId = $faker->randomElement($billingIds);

                $prefix = 'INV-';
                $datePart = now()->format('Ymd');
                $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $invoiceCode = $prefix . $datePart . $randomPart;

                $status = $faker->randomElement([
                    SettlementStatus::Unpaid->value,
                    SettlementStatus::Paid->value,
                    SettlementStatus::Canceled->value,
                ]);

                $dueDate = $faker->dateTimeBetween('-6 months', '+3 months')->format('Y-m-d');
                $paymentMethod = $faker->randomElement(['Bank Transfer', 'Cash', 'Virtual Account', 'E-wallet']);
                $paymentType = $faker->randomElement(['full', 'installment']);

                // Detail amounts
                $details = [
                    ['item_name' => 'Biaya Kuliah', 'item_type' => 'tuition', 'amount' => $faker->numberBetween(2000000, 3000000)],
                    ['item_name' => 'Biaya Admin', 'item_type' => 'admin_fee', 'amount' => $faker->randomElement([5000, 10000, 15000])],
                ];

                // Optionally add a third item
                if ($faker->boolean(50)) {
                    $details[] = ['item_name' => 'Biaya Pengiriman', 'item_type' => 'shipping', 'amount' => $faker->randomElement([10000, 15000, 20000])];
                }


                $totalAmount = collect($details)->sum('amount');

                $invoice = Invoice::create([
                    'uuid' => Str::uuid(),
                    'student_id' => $studentId,
                    'billing_id' => $billingId,
                    'invoice_code' => $invoiceCode,
                    'total_amount' => $totalAmount,
                    'due_date' => $dueDate,
                    'payment_status' => $status,
                    'payment_method' => $status === SettlementStatus::Paid->value ? $paymentMethod : null,
                    'payment_type' => $status === SettlementStatus::Paid->value ? $paymentType : null,
                    'note' => $faker->optional()->sentence(),
                    'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                    'updated_at' => now(),
                ]);

                foreach ($details as $detail) {
                    InvoiceDetail::create([
                        'invoice_id' => $invoice->id,
                        'item_name' => $detail['item_name'],
                        'item_type' => $detail['item_type'],
                        'amount' => $detail['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return collect([$invoice]);
            });

            DB::commit();
            $this->command->info('Invoices created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error creating invoices: ' . $e->getMessage());
            throw $e;
        }
    }
}
