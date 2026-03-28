<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProductColors extends Command
{
    protected $signature = 'app:migrate-product-colors';

    protected $description = 'Migrate product string colors to the product_colors entity relationship.';

    public function handle()
    {
        $this->info('Starting defensive data migration for product colors...');

        $initialVariations = ProductVariation::count();
        $initialImages = ProductImage::count();
        $initialStock = ProductVariation::sum('stock');
        $initialVarsWithoutColorId = ProductVariation::whereNull('product_color_id')->count();
        $initialImagesWithoutColorId = ProductImage::whereNull('product_color_id')->count();

        $this->newLine();
        $this->table(
            ['Metric', 'Before Migration'],
            [
                ['Total Variations', $initialVariations],
                ['Total Images', $initialImages],
                ['Total Stock', $initialStock],
                ['Variations without Color ID', $initialVarsWithoutColorId],
                ['Images without Color ID', $initialImagesWithoutColorId],
            ]
        );
        $this->newLine();

        if ($initialVarsWithoutColorId === 0 && $initialVariations > 0) {
            $this->info('All variations already have a product_color_id. Migration might have run already.');

            return Command::SUCCESS;
        }

        DB::beginTransaction();

        try {
            $products = Product::with(['variations', 'images'])->get();
            $bar = $this->output->createProgressBar(count($products));

            foreach ($products as $product) {
                // 1. Normalize and create unique product colors
                $colorGroups = $product->variations->groupBy(function ($var) {
                    return strtolower(trim($var->color));
                });

                $position = 0;
                $colorIdMap = []; // ['normalized_color' => product_color_id]

                foreach ($colorGroups as $normalizedColor => $variations) {
                    if (empty($normalizedColor)) {
                        // Skip if the variation had no color string
                        // This shouldn't exist due to validation, but defensively handle it
                        continue;
                    }

                    $productColor = ProductColor::create([
                        'product_id' => $product->id,
                        'name' => $normalizedColor,
                        'position' => $position++,
                    ]);

                    $colorIdMap[$normalizedColor] = $productColor->id;
                }

                // 2. Link variations
                foreach ($product->variations as $variation) {
                    $normColor = strtolower(trim($variation->color));

                    if (! isset($colorIdMap[$normColor])) {
                        // If color string was totally empty and wasn't processed
                        if (empty($normColor) && count($colorIdMap) === 1) {
                            // Link to the only available color
                            $variation->update(['product_color_id' => array_values($colorIdMap)[0]]);

                            continue;
                        }
                        throw new Exception("Variation {$variation->id} has unresolved color '{$variation->color}'");
                    }

                    $variation->update(['product_color_id' => $colorIdMap[$normColor]]);
                }

                // 3. Link images
                $hasMultipleColors = count($colorIdMap) > 1;

                foreach ($product->images as $image) {
                    $normColor = strtolower(trim($image->color));

                    if (empty($normColor) && count($colorIdMap) === 1) {
                        // Missing color string, but only 1 color exists. Safe to link.
                        $image->update(['product_color_id' => array_values($colorIdMap)[0]]);
                    } elseif (isset($colorIdMap[$normColor])) {
                        // Exact match found
                        $image->update(['product_color_id' => $colorIdMap[$normColor]]);
                    } elseif (count($colorIdMap) === 1) {
                        // Color string doesn't match precisely, but there's ONLY one color overall for the product
                        // Force associate it so it doesn't get orphaned.
                        $image->update(['product_color_id' => array_values($colorIdMap)[0]]);
                    } else {
                        // Orphaned image finding - color string mismatches and there are multiple colors
                        throw new Exception("Image {$image->id} (Product {$product->id}) has color '{$image->color}' which does not match any variation color.");
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // 4. Verification Check
            $finalVariations = ProductVariation::count();
            $finalImages = ProductImage::count();
            $finalStock = ProductVariation::sum('stock');
            $finalVarsWithoutColorId = ProductVariation::whereNull('product_color_id')->count();
            $finalImagesWithoutColorId = ProductImage::whereNull('product_color_id')->count();

            $this->table(
                ['Metric', 'After Migration'],
                [
                    ['Total Variations', $finalVariations],
                    ['Total Images', $finalImages],
                    ['Total Stock', $finalStock],
                    ['Variations without Color ID', $finalVarsWithoutColorId],
                    ['Images without Color ID', $finalImagesWithoutColorId],
                ]
            );

            if ($finalVariations !== $initialVariations || $initialStock !== $finalStock) {
                throw new Exception('Critical Data Mismatch: Variations or Stock count altered during migration.');
            }

            if ($finalVarsWithoutColorId > 0 || $finalImagesWithoutColorId > 0) {
                throw new Exception("Orphan detected: {$finalVarsWithoutColorId} variations and {$finalImagesWithoutColorId} images still have NULL product_color_id.");
            }

            DB::commit();
            $this->info('Migration completed successfully! No orphaned records.');

        } catch (Exception $e) {
            DB::rollBack();
            $this->error('Migration aborted! Rollback executed.');
            $this->error('Reason: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
