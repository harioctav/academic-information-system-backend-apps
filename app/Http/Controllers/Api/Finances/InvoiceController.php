<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
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
        $invoices = $this->invoiceService
            ->query()
            ->with(['student', 'billing', 'details'])
            ->latest()
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function store(InvoiceStoreRequest $request)
    {
        $invoice = $this->invoiceService->handleStore($request->validated());

        return new InvoiceResource($invoice);
    }

    public function show(string $uuid)
    {
        $invoice = $this->invoiceService->handleShow($uuid);

        return new InvoiceResource($invoice);
    }

    public function update(InvoiceUpdateRequest $request, string $uuid)
    {
        $invoice = $this->invoiceService->handleShow($uuid); // Ambil invoice by UUID
        $updatedInvoice = $this->invoiceService->handleUpdate($request->validated(), $invoice);

        return new InvoiceResource($updatedInvoice);
    }

    public function showByBilling(string $uuid)
    {
        $invoices = $this->invoiceService->handleShowByBilling($uuid);
        return InvoiceResource::collection($invoices);
    }

    public function showByNim(string $nim)
    {
        $invoices = $this->invoiceService->handleShowByNim($nim);
        return InvoiceResource::collection($invoices);
    }

}
