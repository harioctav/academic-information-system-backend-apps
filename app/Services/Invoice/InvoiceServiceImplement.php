<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Invoice\InvoiceRepository;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;

use App\Models\Billing;
use App\Models\Student;
class InvoiceServiceImplement extends ServiceApi implements InvoiceService
{
    protected string $title = "Faktur";
    protected string $create_message = "Data Faktur berhasil dibuat";
    protected string $update_message = "Data Faktur berhasil diperbarui";
    protected string $delete_message = "Data Faktur berhasil dihapus";
    protected string $error_message = "Terjadi kesalahan saat memproses data";

    protected $mainRepository;

    public function __construct(InvoiceRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query()
    {
        return $this->mainRepository->query();
    }

    public function getWhere(array $wheres = [], $columns = '*', $comparisons = '=', $orderBy = null, $orderByType = null)
    {
        return $this->mainRepository->getWhere($wheres, $columns, $comparisons, $orderBy, $orderByType);
    }

    // public function handleStore(array $data): Invoice
    // {
        // $data['uuid'] = Str::uuid()->toString();

        // // Buat invoice utama
        // $invoice = $this->mainRepository->create($data);

        // // Tambahkan detail invoice jika ada
        // if (!empty($data['details']) && is_array($data['details'])) {
        //     $invoice->details()->createMany($data['details']);
        // }

        // return $invoice->load(['student', 'billing', 'details']);
    // }


    public function handleStore(array $data)
    {
        try {
            $data['uuid'] = Str::uuid();

            // Buat invoice utama
            $invoice = $this->mainRepository->create($data);

            // Tambahkan detail invoice jika ada
            if (!empty($data['details']) && is_array($data['details'])) {
                // pastikan setiap detail punya invoice_id
                foreach ($data['details'] as &$detail) {
                    $detail['invoice_id'] = $invoice->id;
                }
                $invoice->details()->createMany($data['details']);
            }

            // return $invoice->load(['student', 'billing', 'details']);
            return $invoice->load(['student', 'billing', 'details']); // return model
        } catch (\Exception $e) {
            // Tangani error jika diperlukan
            throw $this->setMessage($this->error_message)->toJson();
        }
    }

    public function createInvoice($payment)
    {
        $billing = $payment->billing;
        $student = $payment->student;

        // Siapkan data untuk invoice
        $invoiceData = [
            'student_id' => $payment->student_id,
            'billing_id' => $payment->billing_id,
            'payment_id' => $payment->id,
            'total_amount' => $payment->amount_paid,
            'payment_method' => $payment->payment_method,
            'payment_status' => 'paid', // Karena ini dibuat dari payment yang sudah dibayar
            'details' => [
                [
                    'invoice_id' => null, // Akan diisi otomatis setelah invoice dibuat
                    'item_name' => "Pembayaran untuk tagihan: " . $billing->note,
                    'amount' => $payment->amount_paid,
                    'item_type' => 'payment',
                ],
            ],
        ];

        // Buat invoice menggunakan handleStore
        return $this->handleStore($invoiceData);
    }

    public function handleUpdate(array $data, Invoice $invoice): Invoice
    {
        // Update data invoice
        $invoice->update($data);

        // Hapus semua detail sebelumnya, lalu insert ulang
        if (isset($data['details']) && is_array($data['details'])) {
            $invoice->details()->delete();
            $invoice->details()->createMany($data['details']);
        }

        return $invoice->load(['student', 'billing', 'details']);
    }

    public function handleShow(string $uuid): Invoice
    {
        $invoice = $this->mainRepository
            ->query()
            ->where('uuid', $uuid)
            ->with(['student', 'billing', 'details'])
            ->first();

        if (!$invoice) {
            throw $this->setMessage('Invoice not found')->toJson(400);
        }

        return $invoice->toJson();
    }


    public function handleShowByBilling(string $billingUuid)
    {
        $billing = Billing::where('uuid', $billingUuid)->firstOrFail();

        return $this->mainRepository
            ->query()
            ->where('billing_id', $billing->id)
            ->with(['student', 'billing', 'details'])
            ->get(); // Banyak invoice per billing
    }

    public function handleShowByNim(string $nim)
    {
        $student = Student::where('nim', $nim)->firstOrFail();

        return $this->mainRepository
            ->query()
            ->where('student_id', $student->id)
            ->with(['student', 'billing', 'details'])
            ->get(); // Bisa banyak invoice juga
    }
}
