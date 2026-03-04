<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_color_id')->nullable()->constrained('product_colors')->nullOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->decimal('cost_price', 12, 2)->comment('سعر الشراء لهذي الدفعة');
            $table->decimal('quantity_in', 12, 2)->comment('الكمية اللي دخلت');
            $table->decimal('quantity_remaining', 12, 2)->comment('الكمية المتبقية');
            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
