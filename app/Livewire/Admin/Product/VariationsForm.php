<?php

namespace App\Livewire\Admin\Product;

use App\Models\Product;
use Livewire\Component;

class VariationsForm extends Component
{
    public array $colors = [];
    public string $basePrice = '';

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $product->load('variations');
            $grouped = $product->variations->groupBy('color');

            foreach ($grouped as $colorName => $vars) {
                $group = ['name' => $colorName, 'variations' => []];

                foreach ($vars as $v) {
                    $group['variations'][] = [
                        'id' => $v->id,
                        'size' => $v->size,
                        'price' => $v->price,
                        'stock' => (int) $v->stock,
                        'sku' => $v->sku ?? '',
                    ];
                }

                $this->colors[] = $group;
            }
        }

        // Always have at least one color group
        if (empty($this->colors)) {
            $this->addColor();
        }
    }

    /* ──────────────── Color CRUD ──────────────── */

    public function addColor(): void
    {
        $this->colors[] = [
            'name' => '',
            'variations' => [
                $this->emptyVariation(),
            ],
        ];
    }

    public function removeColor(int $index): void
    {
        if (count($this->colors) <= 1) {
            return;
        }

        unset($this->colors[$index]);
        $this->colors = array_values($this->colors);
    }

    /* ──────────────── Variation CRUD ──────────────── */

    public function addVariation(int $colorIndex): void
    {
        $this->colors[$colorIndex]['variations'][] = $this->emptyVariation();
    }

    public function removeVariation(int $colorIndex, int $varIndex): void
    {
        $variations = &$this->colors[$colorIndex]['variations'];

        if (count($variations) <= 1) {
            return;
        }

        unset($variations[$varIndex]);
        $variations = array_values($variations);
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
        }
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
            }
        }
    }

    /* ──────────────── Computed: Flat Array for Form ──────────────── */

    public function getFlatVariationsProperty(): array
    {
        $flat = [];

        foreach ($this->colors as $color) {
            $colorName = $color['name'];

            foreach ($color['variations'] as $v) {
                $flat[] = [
                    'id' => $v['id'] ?? '',
                    'color' => $colorName,
                    'size' => $v['size'] ?? '',
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
            'id' => '',
            'size' => '',
            'price' => '',
            'stock' => '',
            'sku' => '',
        ];
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
