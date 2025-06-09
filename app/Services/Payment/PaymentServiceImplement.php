<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Payment\PaymentRepository;
use Illuminate\Support\Facades\Storage;

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
}
