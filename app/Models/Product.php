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

    /**
     * Parse structured variant objects from the product's `color` JSON column.
     */
    public static function parseVariantsFromColor($rawColor): array
    {
        if (empty($rawColor)) {
            return [];
        }

        $parsed = is_string($rawColor) ? json_decode($rawColor, true) : $rawColor;
        if (!is_array($parsed) || empty($parsed)) {
            return [];
        }

        $values = array_values($parsed);
        if (isset($values[0]) && is_array($values[0]) && array_key_exists('name', $values[0])) {
            return $values;
        }

        return [];
    }

    /**
     * Split a search query into normalized lowercase tokens.
     */
    public static function tokenizeSearchTerm(?string $term): array
    {
        $trimmed = trim((string) $term);
        if ($trimmed === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', mb_strtolower($trimmed, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
        return is_array($parts) ? $parts : [];
    }

    /**
     * Check if every token appears within the given haystack (case-insensitive).
     */
    public static function tokensMatchHaystack(array $tokens, string $haystack): bool
    {
        if (empty($tokens)) {
            return true;
        }

        $lower = mb_strtolower($haystack, 'UTF-8');
        foreach ($tokens as $token) {
            if (mb_strpos($lower, $token, 0, 'UTF-8') === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply a token-based SQL candidate filter across base fields and variant JSON.
     */
    public static function applyTokenSearchFilter($query, ?string $term, string $table = '', array $extraRelations = []): void
    {
        $tokens = self::tokenizeSearchTerm($term);
        if (empty($tokens)) {
            return;
        }

        $prefix = $table !== '' ? ($table . '.') : '';

        foreach ($tokens as $tok) {
            $like = '%' . $tok . '%';
            $escapedSlashLike = str_contains($tok, '/') ? ('%' . str_replace('/', '\\/', $tok) . '%') : null;

            $query->where(function ($q) use ($like, $escapedSlashLike, $prefix, $extraRelations) {
                $q->where($prefix . 'item_name', 'like', $like)
                    ->orWhere($prefix . 'item_code', 'like', $like)
                    ->orWhere($prefix . 'barcode_path', 'like', $like)
                    ->orWhere($prefix . 'color', 'like', $like);

                if ($escapedSlashLike !== null) {
                    $q->orWhere($prefix . 'color', 'like', $escapedSlashLike);
                }

                foreach ($extraRelations as $relation) {
                    $q->orWhereHas($relation, function ($rq) use ($like) {
                        $rq->where('name', 'like', $like);
                    });
                }
            });
        }
    }

    /**
     * Evaluate whether this product and/or specific variants match the search tokens.
     */
    public function matchSearchTermDetails(array $tokens): array
    {
        $variants = self::parseVariantsFromColor($this->color);

        if (empty($tokens)) {
            $allIndexes = array_keys($variants);
            return [
                'matched' => true,
                'base_matched' => true,
                'matched_variant_indexes' => $allIndexes,
                'combined_variant_indexes' => $allIndexes,
                'variants' => $variants,
            ];
        }

        $baseHaystack = trim(
            ($this->item_name ?? '') . ' ' .
            ($this->item_code ?? '') . ' ' .
            ($this->barcode_path ?? '')
        );
        $baseMatched = self::tokensMatchHaystack($tokens, $baseHaystack);

        if (!empty($variants)) {
            $matchedVariantIndexes = [];
            $combinedVariantIndexes = [];

            foreach ($variants as $idx => $v) {
                $vSize = trim((string) ($v['size'] ?? ''));
                $vColor = trim((string) ($v['color'] ?? ''));

                $variantOwnHaystack = trim(
                    ($v['name'] ?? '') . ' ' .
                    (($vSize !== '' && $vSize !== '-') ? $vSize : '') . ' ' .
                    (($vColor !== '' && $vColor !== '-') ? $vColor : '') . ' ' .
                    ($v['barcode'] ?? '') . ' ' .
                    ($v['unit'] ?? '')
                );

                $combinedHaystack = trim($baseHaystack . ' ' . $variantOwnHaystack);

                if (self::tokensMatchHaystack($tokens, $variantOwnHaystack)) {
                    $matchedVariantIndexes[] = $idx;
                    $combinedVariantIndexes[] = $idx;
                } elseif (self::tokensMatchHaystack($tokens, $combinedHaystack)) {
                    $combinedVariantIndexes[] = $idx;
                }
            }

            return [
                'matched' => $baseMatched || !empty($combinedVariantIndexes),
                'base_matched' => $baseMatched,
                'matched_variant_indexes' => $matchedVariantIndexes,
                'combined_variant_indexes' => $combinedVariantIndexes,
                'variants' => $variants,
            ];
        }

        $legacyMatched = false;
        if (!$baseMatched && !empty($this->color)) {
            $decoded = is_string($this->color) ? json_decode($this->color, true) : $this->color;
            if (is_array($decoded)) {
                $flatStrings = array_filter($decoded, 'is_scalar');
                $legacyText = implode(' ', $flatStrings);
            } else {
                $legacyText = (string) $this->color;
            }
            if ($legacyText !== '') {
                $legacyMatched = self::tokensMatchHaystack($tokens, trim($baseHaystack . ' ' . $legacyText));
            }
        }

        return [
            'matched' => $baseMatched || $legacyMatched,
            'base_matched' => $baseMatched,
            'matched_variant_indexes' => [],
            'combined_variant_indexes' => [],
            'variants' => [],
        ];
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
