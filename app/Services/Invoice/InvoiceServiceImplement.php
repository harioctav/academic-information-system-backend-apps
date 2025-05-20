<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Student;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Invoice\InvoiceRepository;

class InvoiceServiceImplement extends ServiceApi implements InvoiceService
{
    protected string $title = "Invoice";
    protected InvoiceRepository $mainRepository;

    public function __construct(InvoiceRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query()
    {
        return $this->mainRepository->query();
    }

    public function getWhere($wheres = [], $columns = '*', $comparisons = '=', $orderBy = null, $orderByType = null)
    {
        return $this->mainRepository->getWhere($wheres, $columns, $comparisons, $orderBy, $orderByType);
    }

    public function handleStore($request)
    {
        try {
            $student = Student::where('nim', $request->nim)->first();

            if (!$student) {
                return $this->setMessage('Student with given NIM not found.')->setStatus(404)->toJson();
            }

            $total_fee = ($request->bank_fee ?? 0) + ($request->subscription_fee ?? 0);

            $invoice = $this->mainRepository->create([
                'student_id' => $student->id,
                'payment_method' => $request->payment_method,
                'bank_fee' => $request->bank_fee ?? 0,
                'subscription_fee' => $request->subscription_fee ?? 0,
                'subscription_code' => $request->subscription_code,
                'total_fee' => $total_fee,
                'billing_code' => $request->billing_code,
                'payment_status' => 'unpaid',
            ]);

            return $this->setMessage("Invoice berhasil dibuat")->setData($invoice)->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleUpdate($request, Invoice $invoice)
    {
        try {
            $invoice->update([
                'payment_status' => $request->payment_status,
            ]);

            return $this->setMessage("Invoice berhasil diperbarui")->setData($invoice)->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleShow($id)
    {
        try {
            $invoice = Invoice::with('student')->findOrFail($id);
            return $this->setMessage("Detail invoice ditemukan")->setData($invoice)->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->setStatus(404)->toJson();
        }
    }
}
