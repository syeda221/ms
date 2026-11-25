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
     * Check whether a string contains any non-ASCII (e.g. Urdu/Arabic) characters.
     */
    public static function hasNonAscii(?string $text): bool
    {
        return preg_match('/[^\x00-\x7F]/', (string) $text) === 1;
    }

    /**
     * Normalize text for search across Latin, Urdu, and Arabic script variations.
     */
    public static function normalizeSearchText(?string $text): string
    {
        $str = trim((string) $text);
        if ($str === '') {
            return '';
        }

        $str = mb_strtolower($str, 'UTF-8');

        // Strip Arabic/Urdu diacritics (harakat) and tatweel
        $str = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $str) ?? $str;
        // Turn zero-width joiners/markers into word boundaries
        $str = str_replace(["\u{200C}", "\u{200D}", "\u{200E}", "\u{200F}"], ' ', $str);

        // Normalize common Urdu/Arabic keyboard character variations & Urdu/Arabic digits
        static $charMap = [
            'ي' => 'ی',
            'ى' => 'ی',
            'ئ' => 'ی',
            'ې' => 'ی',
            'ك' => 'ک',
            'ه' => 'ہ',
            'ھ' => 'ہ',
            'ۂ' => 'ہ',
            'ۃ' => 'ہ',
            'ة' => 'ہ',
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ٱ' => 'ا',
            'ؤ' => 'و',
            'ۓ' => 'ے',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ];
        $str = strtr($str, $charMap);

        return preg_replace('/\s+/u', ' ', trim($str)) ?? '';
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
     * Check if every token appears within the given haystack (case- and script-normalized).
     */
    public static function tokensMatchHaystack(array $tokens, string $haystack): bool
    {
        if (empty($tokens)) {
            return true;
        }

        $normalizedHaystack = self::normalizeSearchText($haystack);
        if ($normalizedHaystack === '') {
            return false;
        }

        $allMatchAsTokens = true;
        $normalizedTokens = [];
        foreach ($tokens as $token) {
            $normTok = self::normalizeSearchText($token);
            if ($normTok === '') {
                continue;
            }
            $normalizedTokens[] = $normTok;
            if (mb_strpos($normalizedHaystack, $normTok, 0, 'UTF-8') === false) {
                $allMatchAsTokens = false;
            }
        }

        if ($allMatchAsTokens) {
            return true;
        }

        // Allow space-insensitive matching (e.g. "سیری لیک" <-> "سیریلیک", "cola next" <-> "colanext")
        if (!empty($normalizedTokens)) {
            $compactQuery = str_replace(' ', '', implode('', $normalizedTokens));
            $compactHaystack = str_replace(' ', '', $normalizedHaystack);
            if ($compactQuery !== '' && mb_strpos($compactHaystack, $compactQuery, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply a token-based SQL candidate filter across base fields and variant JSON.
     * Non-ASCII tokens (e.g. Urdu/Arabic) are verified in PHP so JSON \uXXXX escapes
     * and Urdu/Arabic keyboard character variations are never missed by MySQL LIKE.
     */
    public static function applyTokenSearchFilter($query, ?string $term, string $table = '', array $extraRelations = []): void
    {
        $tokens = self::tokenizeSearchTerm($term);
        if (empty($tokens)) {
            return;
        }

        $prefix = $table !== '' ? ($table . '.') : '';

        foreach ($tokens as $tok) {
            if (self::hasNonAscii($tok)) {
                continue;
            }

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
     * Evaluate whether a product row (Model or stdClass) and/or specific variants match the search tokens.
     */
    public static function matchRowSearchTermDetails(object $row, array $tokens): array
    {
        $rawColor = $row->color ?? null;
        $rawName = (string) ($row->item_name ?? '');
        $rawCode = (string) ($row->item_code ?? '');
        $rawBarcode = (string) ($row->barcode_path ?? '');

        // Fast skip for non-ASCII queries over pure-ASCII rows without \u escapes
        if (!empty($tokens)) {
            $queryHasNonAscii = false;
            foreach ($tokens as $tok) {
                if (self::hasNonAscii($tok)) {
                    $queryHasNonAscii = true;
                    break;
                }
            }
            if ($queryHasNonAscii) {
                $colorStr = is_string($rawColor) ? $rawColor : '';
                if (
                    !self::hasNonAscii($rawName)
                    && !self::hasNonAscii($rawCode)
                    && !self::hasNonAscii($rawBarcode)
                    && !self::hasNonAscii($colorStr)
                    && stripos($colorStr, '\\u') === false
                ) {
                    return [
                        'matched' => false,
                        'base_matched' => false,
                        'matched_variant_indexes' => [],
                        'combined_variant_indexes' => [],
                        'variants' => [],
                    ];
                }
            }
        }

        $variants = self::parseVariantsFromColor($rawColor);

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

        $baseHaystack = trim($rawName . ' ' . $rawCode . ' ' . $rawBarcode);
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
        if (!$baseMatched && !empty($rawColor)) {
            $decoded = is_string($rawColor) ? json_decode($rawColor, true) : $rawColor;
            if (is_array($decoded)) {
                $flatStrings = array_filter($decoded, 'is_scalar');
                $legacyText = implode(' ', $flatStrings);
            } else {
                $legacyText = (string) $rawColor;
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

    /**
     * Evaluate whether this product and/or specific variants match the search tokens.
     */
    public function matchSearchTermDetails(array $tokens): array
    {
        return self::matchRowSearchTermDetails($this, $tokens);
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
