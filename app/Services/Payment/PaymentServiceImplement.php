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
        $uuid = Str::uuid()->toString();

        DB::beginTransaction();

        $proofOfPaymentPath = null; // Initialize path to null

        try {
            // 1. Validate and retrieve related models by their UUIDs
            $student = Student::where('uuid', $data['student_uuid'])->first();
            if (!$student) {
                throw new \Exception('Student not found with provided UUID: ' . $data['student_uuid']);
            }

            $invoice = Invoice::where('uuid', $data['invoice_uuid'])->first();
            if (!$invoice) {
                throw new \Exception('Invoice not found with provided UUID: ' . $data['invoice_uuid']);
            }

            $billing = Billing::where('uuid', $data['billing_uuid'])->first();
            if (!$billing) {
                throw new \Exception('Billing not found with provided UUID: ' . $data['billing_uuid']);
            }

            // 2. Handle Proof of Payment (Base64 Image Upload)
            if (!empty($data['proof_of_payment_base64'])) {
                $base64Image = $data['proof_of_payment_base64'];

                // Check if it's a valid base64 string with a data URI scheme
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]); // png, jpeg, gif, webp

                    // Basic validation for image types
                    if (!in_array($type, ['jpeg', 'jpg', 'gif', 'png', 'webp'])) {
                        throw new \Exception('Unsupported image type for proof of payment. Allowed: jpeg, jpg, gif, png, webp.');
                    }

                    // Decode the base64 string
                    $decodedImage = base64_decode($imageData);
                    if ($decodedImage === false) {
                        throw new \Exception('Failed to decode base64 image data.');
                    }

                    // Generate a unique file name and path
                    $fileName = Str::uuid() . '.' . $type;
                    $filePath = 'payments/proofs/' . $fileName; // Path within the public disk

                    // Store the file to the public disk
                    Storage::disk('public')->put($filePath, $decodedImage);
                    $proofOfPaymentPath = $filePath; // Save the relative path to be stored in DB
                } else {
                    throw new \Exception('Invalid base64 image format for proof of payment. Expected data URI scheme (e.g., data:image/png;base64,...)');
                }
            }

            // 3. Prepare data for Payment model creation
            $paymentData = [
                'uuid' => $uuid, // This UUID is for the new Payment record itself
                'student_id' => $student->id, // Use the resolved student ID
                'invoice_id' => $invoice->id, // Use the resolved invoice ID
                'billing_id' => $billing->id, // Still storing billing_uuid as string
                'payment_method' => $data['payment_method'],
                'payment_plan' => $data['payment_plan'],
                'payment_date' => $data['payment_date'],
                'amount_paid' => $data['amount_paid'],
                'transfer_to' => $data['transfer_to'], // Can be null
                'proof_of_payment_path' => $proofOfPaymentPath, // Stored path or null
                'payment_status' => $data['payment_status'],
                'note' => $data['note'], // Can be null
            ];

            // 4. Create the Payment record
            // Ensure your Payment model has these attributes in its $fillable array
            $payment = Payment::create($paymentData);

            DB::commit(); // Commit the transaction

            return $payment; // Return the created Payment model

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // If an image was partially uploaded before an error, delete it
            if ($proofOfPaymentPath && Storage::disk('public')->exists($proofOfPaymentPath)) {
                Storage::disk('public')->delete($proofOfPaymentPath);
            }
            throw $e; // Re-throw the exception for the controller to handle
        }
    }
}
