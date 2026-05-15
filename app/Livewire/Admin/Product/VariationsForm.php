<?php

namespace App\Livewire\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;

class VariationsForm extends Component
{
    public array $colors = [];

    public string $basePrice = '';

    public string $sizeType = Product::DEFAULT_SIZE_TYPE;

    public array $sizeOptions = [];

    public bool $supportsSize = true;

    public function mount(?Product $product = null, ?string $sizeType = null): void
    {
        if ($this->hydrateFromOldInput()) {
            $this->sizeType = Product::isValidSizeType($sizeType)
                ? $sizeType
                : $this->sizeType;
        } elseif ($product && $product->exists) {
            $this->sizeType = Product::isValidSizeType($sizeType)
                ? $sizeType
                : $product->resolved_size_type;

            $product->load(['colors.variations']);

            foreach ($product->colors as $colorModel) {
                $group = [
                    'uuid' => Str::uuid()->toString(),
                    'id' => $colorModel->id,
                    'name' => $colorModel->name,
                    'variations' => [],
                ];

                foreach ($colorModel->variations as $variation) {
                    $group['variations'][] = [
                        'uuid' => Str::uuid()->toString(),
                        'id' => $variation->id,
                        'size' => Product::normalizeSize($variation->size) ?? '',
                        'price' => $variation->price,
                        'sale_price' => $variation->sale_price,
                        'stock' => (int) $variation->stock,
                        'sku' => $variation->sku ?? '',
                    ];
                }

                $this->colors[] = $group;
            }
        } elseif (Product::isValidSizeType($sizeType)) {
            $this->sizeType = $sizeType;
        }

        $this->refreshSizeState();

        if ($this->colors === []) {
            $this->addColor();
        }

        $this->syncVariationSizesForCurrentType();
        $this->dispatchColorSync();
    }

    #[\Livewire\Attributes\On('size-type-changed')]
    public function updateSizeType(string $sizeType): void
    {
        if (! Product::isValidSizeType($sizeType)) {
            return;
        }

        $this->sizeType = $sizeType;
        $this->refreshSizeState();
        $this->syncVariationSizesForCurrentType();
        $this->validateDuplicates();
    }

    public function addColor(): void
    {
        $this->colors[] = [
            'uuid' => Str::uuid()->toString(),
            'name' => '',
            'variations' => [
                $this->emptyVariation(),
            ],
        ];

        $this->dispatchColorSync();
    }

    public function removeColor(int $index): void
    {
        if (count($this->colors) <= 1) {
            return;
        }

        unset($this->colors[$index]);
        $this->colors = array_values($this->colors);

        $this->dispatchColorSync();
    }

    public function addVariation(int $colorIndex): void
    {
        if (! $this->supportsSize) {
            return;
        }

        $this->colors[$colorIndex]['variations'][] = $this->emptyVariation();
    }

    public function removeVariation(int $colorIndex, int $varIndex): void
    {
        if (count($this->colors[$colorIndex]['variations']) <= 1) {
            return;
        }

        unset($this->colors[$colorIndex]['variations'][$varIndex]);
        $this->colors[$colorIndex]['variations'] = array_values(
            $this->colors[$colorIndex]['variations']
        );
    }

    public function applyBasePrice(): void
    {
        if (! is_numeric($this->basePrice) || $this->basePrice <= 0) {
            return;
        }

        foreach ($this->colors as &$color) {
            foreach ($color['variations'] as &$variation) {
                $variation['price'] = $this->basePrice;
            }
            unset($variation);
        }
        unset($color);
    }

    public function updated($propertyName): void
    {
        if (! str_starts_with($propertyName, 'colors.')) {
            return;
        }

        $parts = explode('.', $propertyName);

        if (count($parts) === 5 && $parts[2] === 'variations') {
            $field = $parts[4];
            $colorIdx = (int) $parts[1];
            $varIdx = (int) $parts[3];

            if ($field === 'size') {
                $this->colors[$colorIdx]['variations'][$varIdx]['size'] = $this->normalizeVariationSize(
                    $this->colors[$colorIdx]['variations'][$varIdx]['size'] ?? ''
                );
                $this->validateDuplicates();
            }

            if ($field === 'price') {
                $value = $this->colors[$colorIdx]['variations'][$varIdx]['price'] ?? '';
                if ($value !== '' && (! is_numeric($value) || $value < 0)) {
                    $this->addError($propertyName, 'El precio debe ser mayor o igual a 0.');
                }
            }

            if ($field === 'stock') {
                $value = $this->colors[$colorIdx]['variations'][$varIdx]['stock'] ?? '';
                if ($value !== '' && (! is_numeric($value) || $value < 0)) {
                    $this->addError($propertyName, 'El stock debe ser mayor o igual a 0.');
                }
            }

            if ($field === 'sale_price') {
                $value = $this->colors[$colorIdx]['variations'][$varIdx]['sale_price'] ?? '';
                $price = $this->colors[$colorIdx]['variations'][$varIdx]['price'] ?? null;

                if ($value !== '' && (! is_numeric($value) || $value <= 0)) {
                    $this->addError($propertyName, 'El precio de oferta debe ser mayor a 0.');
                }

                if ($value !== '' && $price !== '' && is_numeric($value) && is_numeric($price) && (float) $value >= (float) $price) {
                    $this->addError($propertyName, 'El precio de oferta debe ser menor al precio base.');
                }
            }
        }

        if (count($parts) === 3 && $parts[2] === 'name') {
            $this->validateDuplicates();
            $this->dispatchColorSync();
        }
    }

    public function render()
    {
        return view('livewire.admin.product.variations-form');
    }

    private function emptyVariation(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'id' => '',
            'size' => $this->supportsSize ? '' : Product::ONE_SIZE_VALUE,
            'price' => '',
            'sale_price' => '',
            'stock' => '',
            'sku' => '',
        ];
    }

    private function refreshSizeState(): void
    {
        $this->supportsSize = $this->sizeType !== config('product_sizes.one_size_type', Product::ONE_SIZE_TYPE);
        $this->sizeOptions = Product::getAllowedSizes($this->sizeType);
    }

    private function syncVariationSizesForCurrentType(): void
    {
        foreach ($this->colors as $colorIndex => $color) {
            foreach ($color['variations'] as $variationIndex => $variation) {
                if ($this->supportsSize) {
                    $normalized = $this->normalizeVariationSize($variation['size'] ?? '');

                    $this->colors[$colorIndex]['variations'][$variationIndex]['size'] = in_array($normalized, $this->sizeOptions, true)
                        ? $normalized
                        : '';
                } else {
                    $this->colors[$colorIndex]['variations'][$variationIndex]['size'] = Product::ONE_SIZE_VALUE;
                }
            }
        }
    }

    private function normalizeVariationSize(null|string|int $size): string
    {
        return Product::normalizeSize($size) ?? '';
    }

    private function dispatchColorSync(): void
    {
        $names = collect($this->colors)
            ->pluck('name')
            ->filter(fn ($name) => trim($name) !== '')
            ->values()
            ->toArray();

        $this->dispatch('sync-image-colors', colors: $names);
    }

    private function validateDuplicates(): void
    {
        $seen = [];

        foreach ($this->colors as $colorIndex => $color) {
            $colorName = strtolower(trim($color['name']));

            foreach ($color['variations'] as $variationIndex => $variation) {
                $size = strtolower(trim((string) ($variation['size'] ?? '')));

                if ($colorName === '' || $size === '') {
                    continue;
                }

                $key = "{$colorName}|{$size}";

                if (isset($seen[$key])) {
                    $this->addError(
                        "colors.{$colorIndex}.variations.{$variationIndex}.size",
                        'Combinación color + talle duplicada.'
                    );
                } else {
                    $seen[$key] = true;
                }
            }
        }
    }

    private function hydrateFromOldInput(): bool
    {
        /** @var array<int, array<string, mixed>> $oldVariations */
        $oldVariations = old('variations', []);

        if (! is_array($oldVariations) || $oldVariations === []) {
            return false;
        }

        $grouped = [];

        foreach ($oldVariations as $variation) {
            if (! is_array($variation)) {
                continue;
            }

            $colorName = trim((string) ($variation['color'] ?? ''));
            $colorKey = mb_strtolower($colorName, 'UTF-8');

            if (! isset($grouped[$colorKey])) {
                $grouped[$colorKey] = [
                    'uuid' => Str::uuid()->toString(),
                    'id' => $variation['color_id'] ?? '',
                    'name' => $colorName,
                    'variations' => [],
                ];
            }

            $grouped[$colorKey]['variations'][] = [
                'uuid' => Str::uuid()->toString(),
                'id' => $variation['id'] ?? '',
                'size' => Product::normalizeSize($variation['size'] ?? null) ?? '',
                'price' => $variation['price'] ?? '',
                'sale_price' => $variation['sale_price'] ?? '',
                'stock' => $variation['stock'] ?? '',
                'sku' => $variation['sku'] ?? '',
            ];
        }

        if ($grouped === []) {
            return false;
        }

        $this->colors = array_values($grouped);

        return true;
    }
}
