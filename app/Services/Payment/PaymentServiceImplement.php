<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Payment\PaymentRepository;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Billing;
use App\Models\Invoice;


class PaymentServiceImplement extends ServiceApi implements PaymentService
{
    protected PaymentRepository $mainRepository;

    public function __construct(PaymentRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query()
    {
        return $this->mainRepository->query();
    }

    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    ) {
        return $this->mainRepository->getWhere(
            wheres: $wheres,
            columns: $columns,
            comparisons: $comparisons,
            orderBy: $orderBy,
            orderByType: $orderByType
        );
    }


    public function handleStore(array $data): Payment
    {
        $data['uuid'] = Str::uuid()->toString();

        if (isset($data['proof_of_payment'])) {
            $data['proof_of_payment'] = $this->handleBase64Upload($data['proof_of_payment']);
        }

        return Payment::create($data)->load(['student', 'billing']);
    }

    public function handleUpdate(array $data, string $uuid): Payment
    {
        $payment = Payment::where('uuid', $uuid)->firstOrFail();

        if (isset($data['proof_of_payment'])) {
            $data['proof_of_payment'] = $this->handleBase64Upload($data['proof_of_payment']);
        }

        $payment->update($data);
        return $payment->load(['student', 'billing']);
    }

    public function handleShow(string $uuid): Payment
    {
        return Payment::where('uuid', $uuid)->with(['student', 'billing'])->firstOrFail();
    }

    protected function handleBase64Upload(?string $base64, string $directory = 'payments'): ?string
    {
        if (!$base64 || !preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            return null;
        }

        $data = substr($base64, strpos($base64, ',') + 1);
        $data = base64_decode($data);

        $extension = strtolower($type[1]);
        $filename = $directory . '/' . uniqid() . '.' . $extension;


        Storage::disk('public')->put($filename, $data);

        return $filename;
    }


    public function handleSubmit(array $data): Payment
    {
        DB::beginTransaction();

        $uuid = Str::uuid()->toString();
        $proofOfPaymentPath = null;

        try {
            $student = Student::where('uuid', $data['student_uuid'])->first();
            if (!$student) {
                throw new \Exception('Student not found with UUID: ' . $data['student_uuid']);
            }

            $invoice = Invoice::where('uuid', $data['invoice_uuid'])->first();
            if (!$invoice) {
                throw new \Exception('Invoice not found with UUID: ' . $data['invoice_uuid']);
            }

            $billing = Billing::where('uuid', $data['billing_uuid'])->first();
            if (!$billing) {
                throw new \Exception('Billing not found with UUID: ' . $data['billing_uuid']);
            }

            $generateCode = $this->generateCode();

            if (empty($data['proof_of_payment_base64'])) {
                throw new \Exception('Bukti pembayaran (base64) tidak boleh kosong.');
            }

            $base64Image = $data['proof_of_payment_base64'];

            if (!is_string($base64Image)) {
                throw new \Exception('Bukti pembayaran harus berupa string base64.');
            }

            if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                throw new \Exception('Format bukti pembayaran tidak valid. Harus berupa data URI base64 image.');
            }

            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // e.g., png, jpg

            if (!in_array($type, ['jpeg', 'jpg', 'gif', 'png', 'webp'])) {
                throw new \Exception("Tipe gambar '$type' tidak didukung. Gunakan: jpeg, jpg, gif, png, webp.");
            }

            $decodedImage = base64_decode($imageData);
            if ($decodedImage === false) {
                throw new \Exception('Gagal mendecode data base64.');
            }

            // Simpan gambar
            $fileName = "{$generateCode}-{$uuid}.{$type}";
            $filePath = "payments/proofs/{$fileName}";

            Storage::disk('public')->put($filePath, $decodedImage);
            $proofOfPaymentPath = $filePath;
            Log::info('Saving base64 image', [
                'file_path' => $filePath,
                'size' => strlen($decodedImage),
                'type' => $type
            ]);

            $payment = Payment::create([
                'uuid' => $uuid,
                'payment_code' => $generateCode,
                'student_id' => $student->id,
                'invoice_id' => $invoice->id,
                'billing_id' => $billing->id,
                'payment_method' => $data['payment_method'],
                'payment_plan' => $data['payment_plan'],
                'payment_date' => $data['payment_date'],
                'amount_paid' => $data['amount_paid'],
                'transfer_to' => $data['transfer_to'],
                'proof_of_payment' => $proofOfPaymentPath,
                'payment_status' => $data['payment_status'],
                'note' => $data['note'],
            ]);

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($proofOfPaymentPath && Storage::disk('public')->exists($proofOfPaymentPath)) {
                Storage::disk('public')->delete($proofOfPaymentPath);
            }

            throw $e;
        }
    }

    public function generateCode(): string
    {
        $prefix = 'PAY-';
        $datePart = now()->format('Ymd');

        $code = '';
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $code = $prefix . $datePart . $randomPart;

            if (!Payment::where('payment_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception("Gagal menghasilkan kode pembayaran unik setelah {$maxAttempts} percobaan.");
    }
}
