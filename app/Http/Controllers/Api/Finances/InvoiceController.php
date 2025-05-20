<?php
// app/Http/Controllers/Api/Finances/InvoiceController.php
// app/Http/Controllers/Api/Finances/InvoiceController.php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Services\Invoice\InvoiceService;
use App\Http\Requests\Finances\InvoiceStoreRequest;
use App\Http\Requests\Finances\InvoiceUpdateRequest;
use App\Http\Resources\Finances\InvoiceResource;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }


    public function index()
    {
        $invoices = $this->invoiceService->query()->with('student')->latest()->get();
        return InvoiceResource::collection($invoices);
    }

    public function store(InvoiceStoreRequest $request)
    {
        return $this->invoiceService->handleStore($request);
    }

    public function show($id)
    {
        return $this->invoiceService->handleShow($id);
    }

    public function update(InvoiceUpdateRequest $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        return $this->invoiceService->handleUpdate($request, $invoice);
    }
}
