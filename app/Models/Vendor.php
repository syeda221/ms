<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{protected $fillable = ['name', 'email', 'phone', 'address','opening_balance']; 
    use HasFactory;

      public function ledger()
    {
        return $this->hasOne(VendorLedger::class);
    }

    /**
     * Check if vendor has any purchases, returns, payments, bilties, vouchers, or ledger activity
     */
    public function hasTransactions(): bool
    {
        // 1. Any Purchases
        if (\App\Models\Purchase::where('vendor_id', $this->id)->exists()) {
            return true;
        }

        // 2. Any Purchase Returns
        if (class_exists(\App\Models\PurchaseReturn::class) && \App\Models\PurchaseReturn::where('vendor_id', $this->id)->exists()) {
            return true;
        }

        // 3. Any Vendor Payments
        if (\Illuminate\Support\Facades\Schema::hasTable('vendor_payments') && \Illuminate\Support\Facades\DB::table('vendor_payments')->where('vendor_id', $this->id)->exists()) {
            return true;
        }

        // 4. Any Vendor Bilties
        if (\Illuminate\Support\Facades\Schema::hasTable('vendor_bilties') && \Illuminate\Support\Facades\DB::table('vendor_bilties')->where('vendor_id', $this->id)->exists()) {
            return true;
        }

        // 5. Any Payment Vouchers
        if (class_exists(\App\Models\PaymentVoucher::class) && \App\Models\PaymentVoucher::where('party_id', $this->id)->where('type', 'vendor')->exists()) {
            return true;
        }

        // 6. Any Receipts Vouchers (refunds from vendor)
        if (class_exists(\App\Models\ReceiptsVoucher::class) && \App\Models\ReceiptsVoucher::where('party_id', $this->id)->where('type', 'vendor')->exists()) {
            return true;
        }

        // 7. Any Vendor Ledger entries (beyond initial 0 opening balance)
        if (class_exists(\App\Models\VendorLedger::class)) {
            $hasLedger = \App\Models\VendorLedger::where('vendor_id', $this->id)
                ->where('previous_balance', '!=', 0)->exists() || \App\Models\VendorLedger::where('vendor_id', $this->id)->count() > 1;
            if ($hasLedger) {
                return true;
            }
        }

        // 8. Any V2 Voucher lines
        if (class_exists(\App\Models\V2VoucherLine::class)) {
            if (\App\Models\V2VoucherLine::where('party_id', $this->id)->where('party_type', self::class)->exists()) {
                return true;
            }
        }

        return false;
    }
}
