@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
<style>
    :root {
        --erp-bg: #f8fafc;
        --erp-card: #ffffff;
        --erp-border: #e2e8f0;
        --erp-primary: #2563eb;
        --erp-primary-dark: #1d4ed8;
        --erp-success: #10b981;
        --erp-danger: #ef4444;
        --erp-text-main: #0f172a;
        --erp-text-muted: #64748b;
    }

    body {
        background-color: var(--erp-bg);
        color: var(--erp-text-main);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .page-header-box {
        background: #ffffff;
        border: 1px solid var(--erp-border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .erp-box {
        background: var(--erp-card);
        border: 1px solid var(--erp-border);
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .erp-box-header {
        background: #fafbfc;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--erp-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-box-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .erp-box-body {
        padding: 1.25rem;
    }

    .form-label-erp {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569;
        margin-bottom: 0.35rem;
        display: block;
    }

    .form-control-erp, .form-select-erp {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
        color: #0f172a;
    }

    .form-control-erp:focus, .form-select-erp:focus {
        border-color: var(--erp-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Select2 custom skin */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        background: #ffffff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 12px;
        color: #0f172a;
        font-size: 0.9rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px;
    }

    .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }

    /* Product search dropdown styling */
    .prod-search-card {
        padding: 6px 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .prod-search-name {
        font-weight: 600;
        font-size: 0.92rem;
        color: #0f172a;
    }
    .prod-search-meta {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
    }

    /* Search bar wrapper */
    .search-bar-wrap {
        background: #f1f5f9;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        transition: border-color 0.2s;
    }
    .search-bar-wrap:focus-within {
        border-color: var(--erp-primary);
        background: #ffffff;
    }

    /* Return items table */
    .return-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .return-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 0.65rem;
        border-bottom: 1px solid #e2e8f0;
        border-top: none;
        vertical-align: middle;
    }

    .return-table tbody td {
        padding: 0.65rem 0.5rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        background: #ffffff;
    }

    .return-table tbody tr:hover td {
        background-color: #fbfcfe;
    }

    .badge-variant {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .badge-sku {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }

    .table-input {
        height: 38px;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        text-align: center;
        transition: all 0.2s;
    }

    .table-input:focus {
        border-color: var(--erp-primary);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .table-input-pcs {
        color: #1d4ed8;
        background-color: #eff6ff;
        border-color: #bfdbfe;
    }

    .row-total-disp {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        font-family: monospace;
    }

    .btn-delete-row {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ef4444;
        background-color: #fef2f2;
        border: 1px solid #fee2e2;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-delete-row:hover {
        background-color: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* Ledger info box */
    .ledger-info-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 1.1rem;
    }

    .financial-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    .net-amount-banner {
        background: #0f172a;
        color: #ffffff;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .net-amount-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: #34d399;
        font-family: monospace;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid py-3">

            {{-- Top Header --}}
            <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary px-2.5 py-1.5 fs-7"><i class="fas fa-undo-alt"></i> SALE RETURN</span>
                        <h4 class="mb-0 fw-bold text-dark">Standalone / Direct Sale Return</h4>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Bina purane invoice ke direct return enter karein. Customer ka ledger (Credit Note) aur warehouse stock foran update hoga.
                    </p>
                </div>
                <div>
                    <a href="{{ route('sale.return.index') }}" class="btn btn-outline-secondary px-3 py-2 fw-semibold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Returns
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('sale.return.store') }}" method="POST" id="standaloneReturnForm">
                @csrf
                <input type="hidden" name="sale_id" value="">

                {{-- Header Details --}}
                <div class="erp-box">
                    <div class="erp-box-header">
                        <h6 class="erp-box-title"><i class="fas fa-user-tag text-primary"></i> Return Details & Customer</h6>
                        <span class="badge bg-light text-secondary border">Step 1 of 3</span>
                    </div>
                    <div class="erp-box-body">
                        <div class="row g-3">
                            {{-- Default Hidden Warehouse --}}
                            <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ auth()->user()->warehouse_id ?? ($warehouses->first()->id ?? 1) }}">

                            {{-- Customer Selection --}}
                            <div class="col-md-6">
                                <label class="form-label-erp">Select Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">-- Search Customer (Name, ID, Mobile) --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" data-balance="{{ $c->previous_balance ?? $c->opening_balance ?? 0 }}">
                                            {{ $c->customer_name }} {{ $c->mobile ? '('.$c->mobile.')' : '' }} {{ $c->customer_id ? '['.$c->customer_id.']' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-1 d-flex justify-content-between align-items-center" id="customerBalancePreview" style="display:none !important;">
                                    <span class="text-muted small">Current Ledger Balance:</span>
                                    <span class="badge bg-light text-primary border fw-bold" id="customerBalanceVal">Rs. 0.00</span>
                                </div>
                            </div>

                            {{-- Return Date --}}
                            <div class="col-md-6">
                                <label class="form-label-erp">Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" id="return_date" class="form-control form-control-erp" value="{{ date('Y-m-d') }}" required>
                            </div>

                            {{-- Remarks / Reason --}}
                            <div class="col-md-12">
                                <label class="form-label-erp">Return Reason / Notes</label>
                                <input type="text" name="return_reason" class="form-control form-control-erp" placeholder="E.g. Customer returned excess or damaged stock">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Search & Items Table --}}
                <div class="erp-box">
                    <div class="erp-box-header">
                        <h6 class="erp-box-title"><i class="fas fa-boxes text-success"></i> Return Items & Quantities</h6>
                        <span class="badge bg-light text-secondary border">Step 2 of 3</span>
                    </div>
                    <div class="erp-box-body">

                        {{-- Product Search Bar (Just like Sale form with all variants) --}}
                        <div class="search-bar-wrap">
                            <label class="form-label-erp text-primary mb-1">
                                <i class="fas fa-search me-1"></i> Search Product & Variants (Name, Barcode, SKU, Size, Color)
                            </label>
                            <div class="row g-2 align-items-center">
                                <div class="col-md-9">
                                    <select id="globalProductSearch" class="form-select" style="width: 100%;">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="number" id="quickAddQty" class="form-control form-control-erp text-center fw-bold" placeholder="Pieces" value="1" min="1">
                                        <button type="button" class="btn btn-primary fw-bold px-3" id="btnQuickAdd">
                                            <i class="fas fa-plus me-1"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-lightbulb text-warning me-1"></i> Tip: Product ka naam ya variant search karein. Select karne par auto-item table mein add ho jayega.
                            </small>
                        </div>

                        {{-- Items Table --}}
                        <div class="table-responsive border rounded-3 overflow-hidden">
                            <table class="return-table" id="returnItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;" class="text-center">#</th>
                                        <th style="width: 32%;">Product & Variant Details</th>
                                        <th style="width: 13%;" class="text-center">Boxes / Cartons<br><small class="fw-normal text-muted" style="font-size:0.68rem;">Carton qty</small></th>
                                        <th style="width: 13%;" class="text-center">Total Pieces<br><small class="fw-normal text-muted" style="font-size:0.68rem;">Auto sync</small></th>
                                        <th style="width: 14%;" class="text-center">Return Price<br><small class="fw-normal text-muted" style="font-size:0.68rem;">Per Carton / Per Pcs</small></th>
                                        <th style="width: 10%;" class="text-center">Discount</th>
                                        <th style="width: 10%;" class="text-end pe-3">Subtotal</th>
                                        <th style="width: 4%;" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="returnTableBody">
                                    <tr id="emptyRow">
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <div class="py-3">
                                                <i class="fas fa-shopping-basket fa-3x text-secondary opacity-50 mb-2"></i>
                                                <h6 class="fw-bold text-secondary mb-1">Koi Return Item Add Nahi Hua</h6>
                                                <p class="small text-muted mb-0">Upar search bar mein product ka naam ya variant likh kar select karein.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Financial & Customer Ledger Settlement --}}
                <div class="erp-box">
                    <div class="erp-box-header">
                        <h6 class="erp-box-title"><i class="fas fa-calculator text-primary"></i> Settlement & Ledger Impact</h6>
                        <span class="badge bg-light text-secondary border">Step 3 of 3</span>
                    </div>
                    <div class="erp-box-body">
                        <div class="row g-4">
                            {{-- Ledger Information & Refund Option --}}
                            <div class="col-lg-7">
                                <div class="ledger-info-box mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fas fa-check-circle text-success fs-5"></i>
                                        <h6 class="fw-bold text-success mb-0">Customer Ledger (Khata) Auto Update:</h6>
                                    </div>
                                    <p class="small text-secondary mb-2">
                                        Return save hone par customer ke ledger account mein <strong>Credit Note</strong> auto generate hoga aur unka outstanding udhaar (payable balance) deduct ho jayega.
                                    </p>
                                    <div class="form-check form-switch mt-3 pt-2 border-top border-success border-opacity-25">
                                        <input class="form-check-input" type="checkbox" id="toggleCashRefund" style="cursor: pointer;">
                                        <label class="form-check-label fw-bold text-dark" for="toggleCashRefund" style="cursor: pointer;">
                                            Customer ko Cash / Bank Refund dena hai?
                                        </label>
                                    </div>
                                </div>

                                {{-- Payment Drawer / Account section --}}
                                <div id="refundAccountSection" class="p-3 bg-light rounded-3 border" style="display: none;">
                                    <h6 class="fw-bold text-dark mb-2 small text-uppercase"><i class="fas fa-wallet me-1 text-primary"></i> Refund Payment Out:</h6>
                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <label class="form-label-erp">Cash / Bank Account</label>
                                            <select name="payment_account_id[]" id="payment_account_id" class="form-select form-select-erp">
                                                <option value="">-- Select Cash/Bank Account --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->head->name ?? '' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label-erp">Paid Refund Amount</label>
                                            <input type="number" step="any" name="payment_amount[]" id="refund_payment_amount" class="form-control form-control-erp text-end fw-bold" placeholder="0.00" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Financial Calculation Card --}}
                            <div class="col-lg-5">
                                <div class="financial-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-bold small text-uppercase">Items Gross Total:</span>
                                        <span class="fw-bold fs-6 text-dark" id="disp_items_total">Rs. 0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-bold small text-uppercase">Item Discounts:</span>
                                        <span class="text-danger fw-bold fs-6" id="disp_item_discounts">- Rs. 0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="extra_discount" class="text-muted fw-bold small text-uppercase mb-0">Extra Discount:</label>
                                        <input type="number" step="any" name="extra_discount" id="extra_discount" 
                                            class="form-control form-control-sm form-control-erp text-end fw-bold" style="width: 130px;" value="0">
                                    </div>

                                    <div class="net-amount-banner">
                                        <div>
                                            <span class="text-white-50 small text-uppercase d-block fw-bold">Net Return Amount</span>
                                            <span class="small text-white opacity-75">Customer Credit</span>
                                        </div>
                                        <div class="net-amount-val" id="disp_net_amount">Rs. 0.00</div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm py-2.5" id="btnSubmitReturn">
                                            <i class="fas fa-check-circle me-1"></i> Post Standalone Return
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // 1. Customer Select2 with search
    $('#customer_id').select2({
        placeholder: '-- Search Customer (Name, ID, Mobile) --',
        allowClear: true,
        width: '100%'
    });

    $('#customer_id').on('change', function() {
        var opt = $(this).find(':selected');
        var bal = parseFloat(opt.data('balance')) || 0;
        if ($(this).val()) {
            $('#customerBalancePreview').attr('style', 'display: flex !important;');
            $('#customerBalanceVal').text('Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            $('#customerBalancePreview').attr('style', 'display: none !important;');
        }
    });

    // 2. Global Product Search with AJAX and all Variants (Same as Sale Form)
    $('#globalProductSearch').select2({
        placeholder: 'Type product name, barcode, SKU, or variant...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route('products.ajax.search') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        templateResult: formatProductResult,
        templateSelection: formatProductSelection
    });

    function formatProductResult(repo) {
        if (repo.loading) return repo.text;
        var stock = repo.stock !== undefined ? repo.stock : 0;
        var sku = repo.sku || 'N/A';
        var stockVal = parseFloat(repo.stock_pieces !== undefined ? repo.stock_pieces : repo.stock) || 0;
        var badgeClass = stockVal > 0 ? 'bg-success' : 'bg-secondary';

        var title = repo.name || repo.text;
        var unitInfo = repo.unit_name || (repo.size_mode ? repo.size_mode.replace('by_', '') : 'Pcs');
        var ppbInfo = (repo.pieces_per_box > 1) ? ` &bull; ${repo.pieces_per_box} pcs/box` : '';

        return $(`
            <div class="prod-search-card">
                <div>
                    <div class="prod-search-name">${title}</div>
                    <div class="prod-search-meta">
                        <span class="badge-sku me-1">SKU: ${sku}</span>
                        <span>${unitInfo}${ppbInfo}</span>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge ${badgeClass} rounded-pill">Stock: ${stock}</span>
                </div>
            </div>
        `);
    }

    function formatProductSelection(repo) {
        return repo.name || repo.text || 'Type product name, barcode, SKU, or variant...';
    }

    // Auto-add or Quick Add on Select
    $('#globalProductSearch').on('select2:select', function(e) {
        var data = e.params.data;
        if (!data || !data.id) return;

        // Update quick qty label based on mode
        var sm = data.size_mode || 'by_pieces';
        var isCarton = (sm === 'by_cartons' || sm === 'by_carton' || sm === 'by_bandal');
        var ppbLabel = parseFloat(data.pieces_per_box || data.ppb) || 1;
        if (isCarton) {
            $('#quickAddQty').attr('placeholder', 'Cartons').val(1);
            $('#quickAddQty').closest('.input-group').find('.qty-unit-label').text('Ctn');
        } else {
            $('#quickAddQty').attr('placeholder', 'Pieces').val(1);
            $('#quickAddQty').closest('.input-group').find('.qty-unit-label').text('Pcs');
        }

        addProductRow(data);
        // Clear search select
        $(this).val(null).trigger('change');
    });

    $('#btnQuickAdd').on('click', function() {
        var data = $('#globalProductSearch').select2('data');
        if (data && data.length > 0 && data[0].id) {
            addProductRow(data[0]);
            $('#globalProductSearch').val(null).trigger('change');
        } else {
            $('#globalProductSearch').select2('open');
        }
    });

    var rowCounter = 0;

    function addProductRow(data) {
        var parts = data.id.toString().split('|variant|');
        var productId = parts[0].split('|')[0];
        var variantBase64 = parts[1] || (data.variant_data || '');

        var displayName = data.name || data.text || 'Product';
        var sku = data.sku || '';
        var ppb = parseFloat(data.pieces_per_box || data.ppb) || 1;
        var sizeMode = data.size_mode || 'by_pieces';
        var ppm2 = parseFloat(data.m2_of_box || data.ppm2) || 0;

        // Pricing: In carton mode, price should be per carton (piece price * ppb)
        var piecePrice = parseFloat(data.retail_price || data.trade_price || data.price) || 0;
        var isCartonMode = (sizeMode === 'by_cartons' || sizeMode === 'by_carton' || sizeMode === 'by_bandal');
        
        var defaultPrice = piecePrice;
        if (isCartonMode && ppb > 1) {
            defaultPrice = Math.round(piecePrice * ppb * 100) / 100;
        }

        var quickInput = parseFloat($('#quickAddQty').val()) || 1;
        if (quickInput <= 0) quickInput = 1;

        // In carton mode: quickAddQty means cartons entered; pieces = cartons × ppb
        // In piece mode: quickAddQty means pieces directly
        var quickPieces, initialBoxes;
        if (isCartonMode) {
            // treat quick input as cartons
            initialBoxes = quickInput.toFixed(2);
            quickPieces  = Math.round(quickInput * ppb);
        } else {
            quickPieces  = quickInput;
            initialBoxes = (quickPieces / ppb).toFixed(2);
        }

        // Parse variant for display tag
        var variantTag = '';
        if (variantBase64) {
            try {
                var vd = JSON.parse(atob(variantBase64));
                var vName = vd.name || vd.color || '';
                var vSize = (vd.size && vd.size !== '-') ? vd.size : '';
                if (vName || vSize) {
                    variantTag = `<span class="badge-variant me-1"><i class="fas fa-tag me-1"></i>${vName} ${vSize}</span>`;
                }
            } catch(e) {}
        }

        rowCounter++;
        $('#emptyRow').hide();

        // Price label: carton mode shows "per carton", else "per piece"
        var priceLabel = isCartonMode ? `<span class="badge bg-warning text-dark ms-1" style="font-size:0.68rem;">Price per Carton</span>` 
                                      : `<span class="badge bg-light text-secondary ms-1" style="font-size:0.68rem;">Price per Piece</span>`;

        var rowHtml = `
            <tr class="return-item-row" id="row_${rowCounter}">
                <td class="text-center text-muted fw-bold row-index-num">${rowCounter}</td>
                <td>
                    <input type="hidden" name="product_id[]" value="${productId}">
                    <input type="hidden" name="color[]" value="${variantBase64}">
                    <input type="hidden" class="ppb-val" value="${ppb}">
                    <input type="hidden" class="sizemode-val" value="${sizeMode}">
                    <input type="hidden" class="ppm2-val" value="${ppm2}">
                    
                    <div class="fw-bold text-dark fs-6">${displayName}</div>
                    <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                        ${sku ? `<span class="badge-sku">Code: ${sku}</span>` : ''}
                        ${variantTag}
                        <span class="text-muted small">(${ppb} pcs/box)</span>
                        ${priceLabel}
                    </div>
                </td>
                <td class="text-center" style="max-width: 110px;">
                    <input type="number" step="any" class="form-control form-control-sm table-input row-boxes" value="${initialBoxes}" placeholder="0">
                </td>
                <td class="text-center" style="max-width: 110px;">
                    <input type="number" step="any" name="qty[]" class="form-control form-control-sm table-input table-input-pcs row-qty" value="${quickPieces}" required min="0.01" placeholder="0">
                </td>
                <td class="text-center" style="max-width: 130px;">
                    <input type="number" step="any" name="price[]" class="form-control form-control-sm table-input row-price text-end" value="${defaultPrice}" required min="0" placeholder="0.00">
                </td>
                <td class="text-center" style="max-width: 110px;">
                    <input type="number" step="any" name="item_disc[]" class="form-control form-control-sm table-input row-disc text-end" value="0" placeholder="0">
                </td>
                <td class="text-end pe-3">
                    <span class="row-total-disp">0.00</span>
                    <input type="hidden" class="row-subtotal" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn-delete-row btn-remove-row" title="Delete Row">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#returnTableBody').append(rowHtml);
        updateRowIndexes();
        recalcRow($('#row_' + rowCounter));
        recalcTotals();

        // Reset quick qty
        $('#quickAddQty').val(1);
    }

    function updateRowIndexes() {
        $('#returnTableBody tr.return-item-row').each(function(idx) {
            $(this).find('.row-index-num').text(idx + 1);
        });
    }

    // Two-way sync: Boxes -> Pieces
    $(document).on('input', '.row-boxes', function() {
        var row = $(this).closest('tr');
        var ppb = parseFloat(row.find('.ppb-val').val()) || 1;
        var boxes = parseFloat($(this).val()) || 0;
        var pieces = Math.round(boxes * ppb);
        row.find('.row-qty').val(pieces);
        recalcRow(row);
        recalcTotals();
    });

    // Two-way sync: Pieces -> Boxes
    $(document).on('input', '.row-qty', function() {
        var row = $(this).closest('tr');
        var ppb = parseFloat(row.find('.ppb-val').val()) || 1;
        var pieces = parseFloat($(this).val()) || 0;
        var boxes = (pieces / ppb).toFixed(2);
        row.find('.row-boxes').val(boxes);
        recalcRow(row);
        recalcTotals();
    });

    // Price & Discount input handlers
    $(document).on('input', '.row-price, .row-disc', function() {
        var row = $(this).closest('tr');
        recalcRow(row);
        recalcTotals();
    });

    // Remove row
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        if ($('#returnTableBody tr.return-item-row').length === 0) {
            $('#emptyRow').show();
        } else {
            updateRowIndexes();
        }
        recalcTotals();
    });

    // Recalculate individual row subtotal
    function recalcRow(row) {
        var qty   = parseFloat(row.find('.row-qty').val())    || 0;   // total pieces
        var boxes = parseFloat(row.find('.row-boxes').val())  || 0;   // boxes/cartons
        var price = parseFloat(row.find('.row-price').val())  || 0;   // price per unit
        var disc  = parseFloat(row.find('.row-disc').val())   || 0;
        var sizeMode = row.find('.sizemode-val').val();
        var ppm2  = parseFloat(row.find('.ppm2-val').val())   || 0;
        var ppb   = parseFloat(row.find('.ppb-val').val())    || 1;

        var lineTotal = 0;

        if (sizeMode === 'by_size') {
            // Price per m2: qty (pieces) × m2_per_piece × price
            lineTotal = ppm2 * qty * price;

        } else if (sizeMode === 'by_cartons' || sizeMode === 'by_carton' || sizeMode === 'by_bandal') {
            // ✅ Price is PER CARTON/BOX → multiply by boxes (not pieces)
            // boxes field = qty / ppb (kept in sync by two-way binding above)
            lineTotal = boxes * price;

        } else {
            // by_pieces / default: Price per piece × pieces
            lineTotal = qty * price;
        }

        var netLine = Math.max(0, lineTotal - disc);
        row.find('.row-subtotal').val(netLine.toFixed(2));
        row.find('.row-total-disp').text(netLine.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Extra discount input
    $('#extra_discount').on('input', function() {
        recalcTotals();
    });

    var currentNetAmount = 0;

    // Recalculate Grand Totals
    function recalcTotals() {
        var itemsTotal = 0;
        var totalDiscounts = 0;

        $('#returnTableBody tr.return-item-row').each(function() {
            var row = $(this);
            var qty      = parseFloat(row.find('.row-qty').val())    || 0;
            var boxes    = parseFloat(row.find('.row-boxes').val())  || 0;
            var price    = parseFloat(row.find('.row-price').val())  || 0;
            var disc     = parseFloat(row.find('.row-disc').val())   || 0;
            var sizeMode = row.find('.sizemode-val').val();
            var ppm2     = parseFloat(row.find('.ppm2-val').val())   || 0;
            var ppb      = parseFloat(row.find('.ppb-val').val())    || 1;

            var lineGross = 0;
            if (sizeMode === 'by_size') {
                lineGross = ppm2 * qty * price;
            } else if (sizeMode === 'by_cartons' || sizeMode === 'by_carton' || sizeMode === 'by_bandal') {
                // Price is PER CARTON -> boxes * price
                lineGross = boxes * price;
            } else {
                lineGross = qty * price;
            }

            itemsTotal     += lineGross;
            totalDiscounts += disc;
        });

        var extraDisc = parseFloat($('#extra_discount').val()) || 0;
        currentNetAmount = Math.max(0, itemsTotal - totalDiscounts - extraDisc);

        $('#disp_items_total').text('Rs. ' + itemsTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#disp_item_discounts').text('- Rs. ' + totalDiscounts.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#disp_net_amount').text('Rs. ' + currentNetAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        if ($('#toggleCashRefund').is(':checked')) {
            $('#refund_payment_amount').val(currentNetAmount.toFixed(2));
        }
    }

    // Toggle Cash / Bank Refund Section
    $('#toggleCashRefund').on('change', function() {
        if ($(this).is(':checked')) {
            $('#refundAccountSection').slideDown();
            $('#refund_payment_amount').val(currentNetAmount.toFixed(2));
        } else {
            $('#refundAccountSection').slideUp();
            $('#refund_payment_amount').val('0.00');
            $('#payment_account_id').val('');
        }
    });

    // Validation before submit
    $('#standaloneReturnForm').on('submit', function(e) {
        if (!$('#customer_id').val()) {
            e.preventDefault();
            alert('Meharbani karke Customer select karein.');
            return false;
        }

        if (!$('#warehouse_id').val()) {
            e.preventDefault();
            alert('Meharbani karke Warehouse select karein.');
            return false;
        }

        if ($('#returnTableBody tr.return-item-row').length === 0) {
            e.preventDefault();
            alert('Kam az kam aik product add karein.');
            return false;
        }

        if ($('#toggleCashRefund').is(':checked')) {
            var acc = $('#payment_account_id').val();
            var amt = parseFloat($('#refund_payment_amount').val()) || 0;
            if (!acc || amt <= 0) {
                e.preventDefault();
                alert('Cash/Bank refund ke liye Account aur Refund Amount enter karein.');
                return false;
            }
        }

        $('#btnSubmitReturn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing Return...');
    });
});
</script>
@endpush
@endsection
