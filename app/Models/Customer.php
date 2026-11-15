<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id', 'customer_name', 'customer_name_ur', 'cnic', 'filer_type', 'zone',
        'contact_person', 'mobile', 'email_address', 'contact_person_2', 'mobile_2',
        'email_address_2', 'opening_balance', 'balance_range', 'address', 'status',
        'customer_type', 'previous_balance', 'sales_officer_id',
        'payment_reminder_date', 'reminder_snoozed_at', 'reminder_day', 'source'
    ];

    public function salesOfficer()
    {
        return $this->belongsTo(SalesOfficer::class, 'sales_officer_id');
    }

    public function getCustomValue($key)
    {
        return $this->$key ?? ($this->attributes[$key] ?? null);
    }

    /**
     * Check if customer has any sales, returns, payments, vouchers, or ledger activity
     */
    public function hasTransactions(): bool
    {
        // 1. Any Sales
        if (\App\Models\Sale::where('customer_id', $this->id)->exists()) {
            return true;
        }

        // 2. Any Sale Returns
        if (class_exists(\App\Models\SaleReturn::class) && \App\Models\SaleReturn::where('customer_id', $this->id)->exists()) {
            return true;
        }

        // 3. Any Customer Payments
        if (class_exists(\App\Models\CustomerPayment::class) && \App\Models\CustomerPayment::where('customer_id', $this->id)->exists()) {
            return true;
        }

        // 4. Any Receipts Vouchers
        if (class_exists(\App\Models\ReceiptsVoucher::class) && \App\Models\ReceiptsVoucher::where('party_id', $this->id)->whereIn('type', ['customer', 'walkin'])->exists()) {
            return true;
        }

        // 5. Any Customer Ledger transactions (other than initial opening balance)
        if (class_exists(\App\Models\CustomerLedger::class)) {
            $hasLedger = \App\Models\CustomerLedger::where('customer_id', $this->id)
                ->where(function ($q) {
                    $q->whereNull('description')
                      ->orWhere('description', '!=', 'Opening Balance');
                })
                ->exists();
            if ($hasLedger) {
                return true;
            }
        }

        // 6. Any V2 Voucher lines
        if (class_exists(\App\Models\V2VoucherLine::class)) {
            if (\App\Models\V2VoucherLine::where('party_id', $this->id)->where('party_type', self::class)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Polymorphic relationship to journal entries
     */
    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'party');
    }

    /**
     * Get current balance from BalanceService
     */
    public function getPreviousBalanceAttribute()
    {
        // Use BalanceService to calculate the real-time balance
        // including opening balance and journal entries.
        try {
            $balanceService = app(\App\Services\BalanceService::class);
            return $balanceService->getCustomerBalance($this);
        } catch (\Exception $e) {
            // Fallback to column if service fails
            return $this->attributes['previous_balance'] ?? 0;
        }
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (!isset($model->is_synced)) {
                $model->is_synced = 0;
            }
        });
    }
}
