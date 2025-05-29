<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_name',
        'amount',
        'item_type',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
