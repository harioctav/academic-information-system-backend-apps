<?php

namespace App\Services\Payment;

use App\Http\Resources\Finances\PaymentResource;
use App\Models\Payment;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Invoice\InvoiceService;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUpload;

class PaymentServiceImplement extends ServiceApi implements PaymentService
{
    use FileUpload;
    /**
     * set title message api for CRUD
     * @param string $title
     */
    protected string $title = "Pembayaran";
    protected string $create_message = "Data Pembayaran berhasil dibuat";
    protected string $update_message = "Data Pembayaran berhasil diperbarui";
    protected string $delete_message = "Data Pembayaran berhasil dihapus";
    protected string $error_message = "Terjadi kesalahan saat memproses data";

    protected $mainRepository;
    protected $invoiceService;

    public function __construct(PaymentRepository $mainRepository, InvoiceService $invoiceService)
    {
        $this->mainRepository = $mainRepository;
        $this->invoiceService = $invoiceService;
    }

    public function query()
    {
        return $this->mainRepository->query();
    }

    public function handleStore($request)
    {
        try {
            $data = $request->validated();
            $data['uuid'] = Str::uuid()->toString();

            // handle file upload for proof_of_payment
            $photoPath = null;

            if($request->hasFile('proof_of_payment')) {
                $result = $this->upload(
                    file: $request->file('proof_of_payment'),
                    path: 'image/proof_of_payments',
                );

                if ($result['success']) {
                    $photoPath = $result['path'];
                } else {
                    return $this->setMessage($this->error_message)->toJson(500);
                }
            }

            if ($photoPath) {
                $data['proof_of_payment'] = $photoPath;
            }

            $payment = $this->mainRepository->create($data);

            return $this->setMessage($this->create_message)
            ->setData(new PaymentResource($payment->load(['student', 'billing'])))
            ->toJson(200);
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson(500);
        }
    }

    public function handleUpdate(String $status, Payment $payment)
    {
        try {
            // $data = $request->validated();
            $data['payment_status'] = $status;

            $payment->update([
                'payment_status' => $status
            ]);

            if($payment) {
                // create invoice if payment status is paid
                if($payment->payment_status === 'confirmed' && $payment->billing && !$payment->billing->invoice_id) {
                    $this->invoiceService->createInvoice($payment);
                }
            }

            return $this->setMessage($this->update_message)
                ->setData([new PaymentResource($payment->load(['student', 'billing', 'invoice']))])
                ->toJson(200);
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson(500);
        }
    }

    public function handleShow(string $uuid): Payment
    {
        return Payment::where('uuid', $uuid)->with(['student', 'billing', 'invoice'])->firstOrFail();
    }

    // protected function handleBase64Upload(?string $base64, string $directory = 'payments'): ?string
    // {
    //     if (!$base64 || !preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
    //         return null;
    //     }

    //     $data = substr($base64, strpos($base64, ',') + 1);
    //     $data = base64_decode($data);

    //     $extension = strtolower($type[1]);
    //     $filename = $directory . '/' . uniqid() . '.' . $extension;


    //     Storage::disk('public')->put($filename, $data);

    //     return $filename;
    // }
}
