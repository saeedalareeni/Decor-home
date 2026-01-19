<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string("item_type"); // ستاره او بسيط

            $table->integer('quantity')->default(1);
            $table->decimal('sell_price', 12, 2)->default(0);  // سعر البيع للزبون
            $table->decimal('total_cost', 12, 2)->default(0);  // تكلفة هذا العنصر
            $table->decimal('profit', 12, 2)->default(0);      // الربح

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
