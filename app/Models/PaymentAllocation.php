<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'invoice_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'invoice_id');
    }

    public function receiptVoucher()
    {
        return $this->belongsTo(ReceiptsVoucher::class, 'voucher_id');
    }

    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class, 'voucher_id');
    }
}
