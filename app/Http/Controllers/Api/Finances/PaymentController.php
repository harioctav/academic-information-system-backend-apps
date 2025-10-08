<?php

namespace App\Http\Controllers\Api\Finances;

use App\Helpers\SearchHelper;
use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finances\PaymentRequest;
use App\Http\Requests\Finances\PaymentUpdateRequest;
use App\Http\Requests\Finances\PaymentStatusRequest;
use App\Http\Resources\Finances\PaymentResource;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
  protected $paymentService;

  public function __construct(PaymentService $paymentService)
  {
    $this->paymentService = $paymentService;
  }

  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->paymentService->query(),
      request: $request,
      searchableFields: [
        'payment_method',
        'payment_plan',
        'amount_paid',
        'transfer_to',
        'note'
      ],
      sortableFields: [
        'payment_method',
        'payment_plan',
        'payment_status',
        'amount_paid',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'payment_method',
        'payment_plan',
        'payment_status'
      ]
    );

    $perPage = $request->input('per_page', 10);
    $result = $query->with(['student', 'billing'])->latest();

    return PaymentResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  public function store(PaymentRequest $request): JsonResponse
  {
    return $this->paymentService->handleStore($request);
  }

  public function show(Payment $payment): PaymentResource
  {
    $payment = $this->paymentService->handleShow($payment->uuid);
    return new PaymentResource($payment);
  }

  public function update(PaymentUpdateRequest $request, Payment $payment): JsonResponse
  {
    return $this->paymentService->handleUpdate($request, $payment);
  }

  /**
   * Update the payment status of the specified payment.
   */
  public function status(PaymentStatusRequest $request, Payment $payment): JsonResponse
  {
    return $this->paymentService->handleUpdateStatus($request->input('payment_status'), $payment);
  }

  public function destroy(Payment $payment): JsonResponse
  {
    return $this->paymentService->handleDelete($payment);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:payments,uuid'
    ]);

    return $this->paymentService->handleBulkDelete($request->ids);
  }
}
