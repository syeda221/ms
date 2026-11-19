<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ['unit_name', 'is_carton'];

    public function getUnitNameAttribute()
    {
        if ($this->relationLoaded('unit') && $this->unit) {
            return $this->unit->name;
        }
        if (!empty($this->unit_id)) {
            $u = Unit::find($this->unit_id);
            if ($u) return $u->name;
        }
        if ($this->size_mode === 'by_bandal') return 'Bndl';
        if ($this->size_mode === 'by_cartons') return 'Ctn';
        if ($this->size_mode === 'by_pieces') return 'Pcs';
        $u = Unit::where('slug', $this->size_mode)->first();
        if ($u) return $u->name;
        return 'Ctn';
    }

    public function getIsCartonAttribute()
    {
        return self::isCartonMode($this->size_mode);
    }

    // protected $fillable = [
    //     'creater_id', 'category_id', 'sub_category_id', 'item_code', 'item_name', 'size',
    //     'opening_carton_quantity', 'carton_quantity', 'loose_pieces', 'pcs_in_carton',
    //     'wholesale_price', 'retail_price', 'initial_stock', 'alert_quantity'
    // ];
    // app/Models/Product.php
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stocks', 'product_id', 'warehouse_id')
            ->withPivot('quantity', 'price', 'remarks');
    }

    // app/Models/Product.php

    public function activeDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id')
            ->where('status', 1); // only active discount
    }

    public function category_relation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category_relation()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(\App\Models\WarehouseStock::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeWithAvailable($q)
    {
        return $q->withSum('movements as available_qty', 'qty'); // sum of ledger
    }

    public function webImages()
    {
        return $this->hasMany(ProductWebImage::class)->orderBy('sort_order', 'asc');
    }

    public static function isCartonMode($sizeMode)
    {
        if (empty($sizeMode)) return false;
        if (in_array($sizeMode, ['by_cartons', 'by_bandal'])) return true;
        if (in_array($sizeMode, ['by_pieces', 'by_size', 'by_kg', 'by_gm', 'by_ton', 'by_meter', 'by_feet'])) return false;

        $unit = Unit::where('slug', $sizeMode)->first();
        if ($unit) {
            return ($unit->type ?? 'carton') === 'carton';
        }
        return true; // Any custom unit defaults to carton-like functionality as requested
    }

    public static function isWeightMode($sizeMode)
    {
        if (empty($sizeMode)) return false;
        if (in_array($sizeMode, ['by_kg', 'by_gm', 'by_ton'])) return true;
        $unit = Unit::where('slug', $sizeMode)->first();
        return $unit && ($unit->type ?? '') === 'weight';
    }

    public function isCartonType()
    {
        return self::isCartonMode($this->size_mode);
    }

    protected static function booted()
    {
        static::saved(function ($product) {
            // Naya product create hone par fauran alert na bhejein (kyunke stock 0 hota hai)
            if ($product->wasRecentlyCreated) {
                return;
            }
            
            // Sirf tab alert check karein jab alert_quantity ya alert_carton_quantity update hui ho
            if (!$product->isDirty('alert_quantity') && !$product->isDirty('alert_carton_quantity')) {
                return;
            }

            $alertQty = $product->alert_quantity;
            if (is_null($alertQty) && !is_null($product->alert_carton_quantity)) {
                $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                $alertQty = $product->alert_carton_quantity * $ppb;
            }
            
            if (!is_null($alertQty) && $alertQty > 0) {
                $totalPieces = \App\Models\WarehouseStock::where('product_id', $product->id)->sum('total_pieces');
                if ($totalPieces <= $alertQty) {
                    \App\Models\SystemNotification::createStockAlertNotification($product, $totalPieces);
                }
            }
        });
    }
}
