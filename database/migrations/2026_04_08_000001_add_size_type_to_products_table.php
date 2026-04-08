<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const DEFAULT_SIZE_TYPE = 'alpha';

    private const ONE_SIZE_TYPE = 'one_size';

    private const ONE_SIZE_VALUE = 'UNICO';

    private const SIZE_SCHEMAS = [
        'alpha' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
        'numeric_1_5' => ['1', '2', '3', '4', '5'],
        'numeric_36_48' => ['36', '38', '40', '42', '44', '46', '48'],
        'one_size' => ['UNICO'],
    ];

    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size_type')->nullable()->after('description');
        });

        $products = DB::table('products')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select('products.id', 'categories.supports_size')
            ->orderBy('products.id')
            ->get();

        foreach ($products as $product) {
            $sizes = DB::table('product_variations')
                ->where('product_id', $product->id)
                ->pluck('size')
                ->filter()
                ->map(fn ($size) => $this->normalizeSize($size))
                ->filter()
                ->unique()
                ->values()
                ->all();

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'size_type' => $this->inferSizeType($sizes, $product->supports_size),
                ]);

            $variations = DB::table('product_variations')
                ->where('product_id', $product->id)
                ->select('id', 'size')
                ->get();

            foreach ($variations as $variation) {
                $normalized = $this->normalizeSize($variation->size);

                if ($normalized === null || $normalized === '') {
                    continue;
                }

                DB::table('product_variations')
                    ->where('id', $variation->id)
                    ->update(['size' => $normalized]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('size_type');
        });
    }

    private function inferSizeType(array $sizes, ?bool $supportsSize): string
    {
        if ($sizes === []) {
            return $supportsSize === false ? self::ONE_SIZE_TYPE : self::DEFAULT_SIZE_TYPE;
        }

        foreach (self::SIZE_SCHEMAS as $type => $allowedSizes) {
            if (count(array_diff($sizes, $allowedSizes)) === 0) {
                return $type;
            }
        }

        return $supportsSize === false ? self::ONE_SIZE_TYPE : self::DEFAULT_SIZE_TYPE;
    }

    private function normalizeSize(null|string|int $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace("\xC2\xA0", ' ', (string) $value);
        $normalized = trim($normalized);

        if ($normalized === '') {
            return '';
        }

        $normalized = mb_strtoupper(Str::ascii($normalized), 'UTF-8');

        if ($normalized === 'UNICO') {
            return self::ONE_SIZE_VALUE;
        }

        return $normalized;
    }
};
