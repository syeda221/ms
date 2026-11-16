<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

function testResolve($id) {
    echo "=== Testing ID: {$id} ===\n";
    $partyTransfer = \App\Models\VoucherMaster::where('id', $id)
        ->whereIn('voucher_type', [\App\Models\VoucherMaster::TYPE_JOURNAL, \App\Models\VoucherMaster::TYPE_CONTRA, 'transfer'])
        ->where(function($q) {
            $q->where('remarks', 'like', '%Party Transfer:%')
              ->orWhere('remarks', 'like', '%Internal Transfer:%');
        })
        ->first();

    if ($partyTransfer) {
        echo "Resolved to Party/Internal Transfer!\n";
        return;
    }

    $voucher = \App\Models\ReceiptsVoucher::find($id);
    if (! $voucher && is_string($id)) {
        $voucher = \App\Models\ReceiptsVoucher::where('rvid', $id)->first();
    }

    if (! $voucher) {
        $vm = \App\Models\VoucherMaster::find($id);
        if ($vm) {
            if (preg_match('/(?:Ref:\s*|#)(RVID-[0-9]+)/i', $vm->remarks ?? '', $m)) {
                $voucher = \App\Models\ReceiptsVoucher::where('rvid', $m[1])->first();
            }
            if (! $voucher && $vm->voucher_no) {
                $voucher = \App\Models\ReceiptsVoucher::where('rvid', $vm->voucher_no)->first();
            }
        }
    }

    if ($voucher) {
        echo "Resolved ReceiptsVoucher ID: {$voucher->id}, RVID: {$voucher->rvid}\n";
        $party = \App\Models\Customer::find($voucher->party_id);
        echo "Customer: " . ($party ? $party->customer_name : 'N/A') . "\n";
        echo "Voucher Total: {$voucher->total_amount}\n";

        // Find ledger
        $ledgerEntry = \App\Models\CustomerLedger::where('customer_id', $voucher->party_id)
            ->where(function($q) use ($voucher) {
                $q->where('description', 'like', "%{$voucher->rvid}%");
                $numOnly = preg_replace('/[^0-9]/', '', $voucher->rvid);
                if ($numOnly) {
                    $q->orWhere('description', 'like', "%RVID-{$numOnly}%")
                      ->orWhere('description', 'like', "%RVID-0{$numOnly}%");
                }
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($ledgerEntry) {
            $previousBalance = (float) $ledgerEntry->previous_balance;
            $balanceAfter = (float) $ledgerEntry->closing_balance;
            echo "From Ledger ID {$ledgerEntry->id}:\n";
            echo "  Previous Balance: {$previousBalance}\n";
            echo "  Amount Received:  {$voucher->total_amount}\n";
            echo "  Remaining Balance: {$balanceAfter}\n";
        } else {
            echo "Ledger entry NOT found!\n";
        }
    } else {
        echo "Voucher NOT found!\n";
    }
    echo "\n";
}

testResolve(120); // ReceiptsVoucher 120
testResolve(401); // VoucherMaster 401 (points to RVID-0120)
testResolve(119); // ReceiptsVoucher 119
testResolve(110); // ReceiptsVoucher 110
