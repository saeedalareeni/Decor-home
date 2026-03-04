<?php

use App\Http\Controllers\Api\ProductBatchesController;
use Illuminate\Support\Facades\Route;

Route::get('products/{product}/batches', [ProductBatchesController::class, 'index']);
