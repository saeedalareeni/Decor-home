<?php

namespace Database\Seeders;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\productColor;
use Illuminate\Database\Seeder;

class InventoryBatchSeeder extends Seeder
{
    /**
     * For existing products with stock > 0, create an initial batch.
     * - Products without colors: one batch using product.stock and product.cost_price.
     * - Products with colors: one batch per color using product_color.stock and product.cost_price.
     */
    public function run(): void
    {
        Product::query()->with('colors')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                $hasColors = $product->colors->isNotEmpty();

                if ($hasColors) {
                    foreach ($product->colors as $color) {
                        $stock = (float) ($color->stock ?? 0);
                        if ($stock <= 0) {
                            continue;
                        }
                        InventoryBatch::create([
                            'product_id' => $product->id,
                            'product_color_id' => $color->id,
                            'invoice_item_id' => null,
                            'cost_price' => (float) ($product->cost_price ?? 0),
                            'quantity_in' => $stock,
                            'quantity_remaining' => $stock,
                            'received_at' => $product->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                            'notes' => 'دفعة أولية (Seeder)',
                        ]);
                    }
                } else {
                    $stock = (float) ($product->stock ?? 0);
                    if ($stock <= 0) {
                        continue;
                    }
                    InventoryBatch::create([
                        'product_id' => $product->id,
                        'product_color_id' => null,
                        'invoice_item_id' => null,
                        'cost_price' => (float) ($product->cost_price ?? 0),
                        'quantity_in' => $stock,
                        'quantity_remaining' => $stock,
                        'received_at' => $product->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                        'notes' => 'دفعة أولية (Seeder)',
                    ]);
                }
            }
        });
    }
}
