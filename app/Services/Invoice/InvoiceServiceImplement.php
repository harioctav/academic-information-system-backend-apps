<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Invoice\InvoiceRepository;
use App\Services\Invoice\InvoiceService;

use App\Models\Billing;
use App\Models\Student;
class InvoiceServiceImplement extends ServiceApi implements InvoiceService
{
    protected InvoiceRepository $mainRepository;

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

    public function handleStore(array $data): Invoice
    {
        $data['uuid'] = Str::uuid()->toString();

        // Buat invoice utama
        $invoice = $this->mainRepository->create($data);

        // Tambahkan detail invoice jika ada
        if (!empty($data['details']) && is_array($data['details'])) {
            $invoice->details()->createMany($data['details']);
        }

        return $invoice->load(['student', 'billing', 'details']);
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
        return $this->mainRepository
            ->query()
            ->where('uuid', $uuid)
            ->with(['student', 'billing', 'details'])
            ->firstOrFail();
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
