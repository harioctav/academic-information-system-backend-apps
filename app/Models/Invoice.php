<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'student_id',
        'billing_id',
        'payment_id',
        'total_amount',
        'due_date',
        'payment_status',
        'payment_method',
        'payment_type',
        'note',
    ];

    protected $casts = [
        'bank_fee' => 'decimal:2',
        'subscription_fee' => 'decimal:2',
        'payment_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'payment_status' => 'string', // enum disimpan sebagai string
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
