<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'invoice_id',
        'billing_id',
        'payment_method',
        'payment_plan',
        'payment_date',
        'amount_paid',
        'transfer_to',
        'proof_of_payment',
        'payment_status',
        'note'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
