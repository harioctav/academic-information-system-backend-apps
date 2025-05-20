<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;
use LaravelEasyRepository\Service;
use App\Repositories\Payment\PaymentRepository;

class PaymentServiceImplement extends Service implements PaymentService
{
    protected PaymentRepository $mainRepository;

    public function __construct(PaymentRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function getAllPaginated($perPage = 10)
    {
        return $this->mainRepository->getWithRelationsPaginated($perPage);
    }

    public function showById($id)
    {
        return $this->mainRepository->findWithRelations($id);
    }

    public function handleStore($request)
    {
        $validated = $request->validated();
        $validated['uuid'] = Str::uuid();

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment'] = $request->file('proof_of_payment')->store('payment_proofs', 'public');
        }

        return $this->mainRepository->create($validated);
    }

    public function handleUpdate($request, $id)
    {
        $payment = $this->mainRepository->find($id);

        $validated = $request->validated();

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment'] = $request->file('proof_of_payment')->store('payment_proofs', 'public');
        }

        $payment->update($validated);

        return $payment;
    }

    public function handleDelete($id)
    {
        $payment = $this->mainRepository->find($id);
        return $payment->delete();
    }
}
