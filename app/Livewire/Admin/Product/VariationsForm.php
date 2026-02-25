<?php

namespace App\Livewire\Admin\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;

class VariationsForm extends Component
{
    public array $colors = [];
    public string $basePrice = '';
    public bool $supportsSize = true;

    public function mount(?Product $product = null, ?int $categoryId = null): void
    {
        if ($product && $product->exists) {
            $this->supportsSize = $product->category->supports_size ?? true;
            $product->load(['colors.variations']);

            foreach ($product->colors as $colorModel) {
                $group = [
                    'uuid' => Str::uuid()->toString(),
                    'id' => $colorModel->id,
                    'name' => $colorModel->name,
                    'variations' => [],
                ];

                foreach ($colorModel->variations as $v) {
                    $group['variations'][] = [
                        'uuid' => Str::uuid()->toString(),
                        'id' => $v->id,
                        'size' => $v->size,
                        'price' => $v->price,
                        'stock' => (int) $v->stock,
                        'sku' => $v->sku ?? '',
                    ];
                }

                $this->colors[] = $group;
            }
        } elseif ($categoryId) {
            $cat = Category::find($categoryId);
            $this->supportsSize = $cat ? ($cat->supports_size ?? true) : true;
        }

        // Always have at least one color group
        if (empty($this->colors)) {
            $this->addColor();
        }

        $this->dispatchColorSync();
    }

    /* ──────────────── Category Change Listener ──────────────── */

    #[\Livewire\Attributes\On('category-changed')]
    public function updateCategory(int $categoryId): void
    {
        $cat = Category::find($categoryId);
        $this->supportsSize = $cat ? ($cat->supports_size ?? true) : true;

        // Si la categoría no soporta talle, dejar solo una variación por color
        if (!$this->supportsSize) {
            foreach ($this->colors as $cIdx => $color) {
                if (count($color['variations']) > 1) {
                    $this->colors[$cIdx]['variations'] = [
                        $color['variations'][0],
                    ];
                }
            }
        }
    }

    /* ──────────────── Color CRUD ──────────────── */

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

    /* ──────────────── Variation CRUD ──────────────── */

    public function addVariation(int $colorIndex): void
    {
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

    /* ──────────────── Base Price Helper ──────────────── */

    public function applyBasePrice(): void
    {
        if (!is_numeric($this->basePrice) || $this->basePrice <= 0) {
            return;
        }

        foreach ($this->colors as &$color) {
            foreach ($color['variations'] as &$v) {
                $v['price'] = $this->basePrice;
            }
            unset($v); // liberar referencia al último elemento
        }
        unset($color); // liberar referencia al último color
    }

    /* ──────────────── Real-time Validation ──────────────── */

    public function updated($propertyName): void
    {
        // Validate individual fields as they change
        if (str_starts_with($propertyName, 'colors.')) {
            $parts = explode('.', $propertyName);

            // colors.{colorIdx}.variations.{varIdx}.{field}
            if (count($parts) === 5 && $parts[2] === 'variations') {
                $field = $parts[4];
                $colorIdx = (int) $parts[1];
                $varIdx = (int) $parts[3];

                if ($field === 'price') {
                    $value = $this->colors[$colorIdx]['variations'][$varIdx]['price'] ?? '';
                    if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                        $this->addError($propertyName, 'El precio debe ser mayor o igual a 0.');
                    }
                }

                if ($field === 'stock') {
                    $value = $this->colors[$colorIdx]['variations'][$varIdx]['stock'] ?? '';
                    if ($value !== '' && (!is_numeric($value) || $value < 0)) {
                        $this->addError($propertyName, 'El stock debe ser mayor o igual a 0.');
                    }
                }

                // Check duplicate color + size
                if ($field === 'size') {
                    $this->validateDuplicates();
                }
            }

            // colors.{colorIdx}.name — also check duplicates
            if (count($parts) === 3 && $parts[2] === 'name') {
                $this->validateDuplicates();
                $this->dispatchColorSync();
            }
        }
    }

    /* ──────────────── Computed: Flat Array for Form ──────────────── */

    public function getFlatVariationsProperty(): array
    {
        $flat = [];

        foreach ($this->colors as $color) {
            $colorName = $color['name'];
            $colorId = $color['id'] ?? null;

            foreach ($color['variations'] as $v) {
                $flat[] = [
                    'id' => $v['id'] ?? '',
                    'color_id' => $colorId,
                    'color' => $colorName,
                    'size' => $this->supportsSize ? ($v['size'] ?? '') : 'Único',
                    'price' => $v['price'] ?? '',
                    'stock' => $v['stock'] ?? '',
                    'sku' => $v['sku'] ?? '',
                ];
            }
        }

        return $flat;
    }

    /* ──────────────── Render ──────────────── */

    public function render()
    {
        return view('livewire.admin.product.variations-form');
    }

    /* ──────────────── Helpers ──────────────── */

    private function emptyVariation(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'id' => '',
            'size' => '',
            'price' => '',
            'stock' => '',
            'sku' => '',
        ];
    }

    private function dispatchColorSync(): void
    {
        $names = collect($this->colors)
            ->pluck('name')
            ->filter(fn($n) => trim($n) !== '')
            ->values()
            ->toArray();

        $this->dispatch('sync-image-colors', colors: $names);
    }

    private function validateDuplicates(): void
    {
        $seen = [];

        foreach ($this->colors as $cIdx => $color) {
            $colorName = strtolower(trim($color['name']));

            foreach ($color['variations'] as $vIdx => $v) {
                $size = strtolower(trim($v['size'] ?? ''));

                if ($colorName === '' || $size === '') {
                    continue;
                }

                $key = "{$colorName}|{$size}";

                if (isset($seen[$key])) {
                    $this->addError(
                        "colors.{$cIdx}.variations.{$vIdx}.size",
                        'Combinación color + talle duplicada.'
                    );
                } else {
                    $seen[$key] = true;
                }
            }
        }
    }
}
