<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBatchesController extends Controller
{
    /**
     * GET /api/products/{product}/batches
     * Optional query: product_color_id
     * Returns only batches with quantity_remaining > 0.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $productColorId = $request->query('product_color_id');
        $query = InventoryBatch::query()
            ->where('product_id', $product->id)
            ->where('quantity_remaining', '>', 0);

        if ($productColorId !== null && $productColorId !== '') {
            $query->where('product_color_id', $productColorId);
        } else {
            $query->whereNull('product_color_id');
        }

        $batches = $query->orderBy('received_at')->orderBy('id')->get();

        $data = $batches->map(fn (InventoryBatch $b) => [
            'id' => $b->id,
            'cost_price' => (float) $b->cost_price,
            'quantity_remaining' => (float) $b->quantity_remaining,
            'received_at' => $b->received_at?->format('Y-m-d'),
            'label' => sprintf(
                'سعر الجملة: %s ₪ (متبقي: %s)',
                number_format((float) $b->cost_price, 2),
                number_format((float) $b->quantity_remaining, 2)
            ),
        ])->values()->all();

        return response()->json($data);
    }
}
