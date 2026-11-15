<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add paid_amount, due_amount, payment_status to sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0.00)->after('total_net');
            }
            if (!Schema::hasColumn('sales', 'due_amount')) {
                $table->decimal('due_amount', 15, 2)->default(0.00)->after('paid_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('due_amount');
            }
        });

        // Initialize existing sales data
        DB::statement("
            UPDATE sales 
            SET 
                paid_amount = LEAST(total_net, (COALESCE(cash, 0) + COALESCE(card, 0))),
                due_amount = GREATEST(0, total_net - (COALESCE(cash, 0) + COALESCE(card, 0))),
                payment_status = CASE 
                    WHEN (total_net - (COALESCE(cash, 0) + COALESCE(card, 0))) <= 0 THEN 'paid'
                    WHEN (COALESCE(cash, 0) + COALESCE(card, 0)) > 0 THEN 'partial'
                    ELSE 'unpaid'
                END
            WHERE total_net IS NOT NULL
        ");

        // 2. Add payment_status to purchases if not present
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('due_amount');
            }
        });

        // Initialize existing purchases payment_status
        DB::statement("
            UPDATE purchases 
            SET payment_status = CASE 
                WHEN due_amount <= 0 AND net_amount > 0 THEN 'paid'
                WHEN paid_amount > 0 THEN 'partial'
                ELSE 'unpaid'
            END
        ");

        // 3. Create payment_allocations table
        if (!Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_type', 30); // 'receipt' or 'payment'
                $table->unsignedBigInteger('voucher_id');
                $table->string('voucher_no', 100)->nullable(); // RVID-xxxx or PVID-xxxx
                $table->string('invoice_type', 30); // 'sale' or 'purchase'
                $table->unsignedBigInteger('invoice_id');
                $table->string('invoice_no', 100)->nullable();
                $table->decimal('amount', 15, 2);
                $table->date('date');
                $table->timestamps();

                $table->index(['voucher_type', 'voucher_id']);
                $table->index(['invoice_type', 'invoice_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('sales', 'due_amount')) {
                $table->dropColumn('due_amount');
            }
            if (Schema::hasColumn('sales', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
