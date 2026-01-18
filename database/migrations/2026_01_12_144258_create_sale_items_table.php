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
            $table->foreignId("sale_id")->constrained()->cascadeOnDelete();
            $table->foreignId("product_id")->constrained()->cascadeOnDelete();
            $table->foreignId('product_color_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('ring_cost', 10, 2)->default(0);   // تكلفة الحلق
            $table->decimal('tailor_cost', 10, 2)->default(0); // تكلفة الخياط
            $table->decimal('extra_cost', 10, 2)->default(0);  // أي تكلفة إضافية

            $table->decimal("quantity", 10, 2);
            $table->decimal("unit_price", 10, 2);
            $table->decimal("total_price", 10, 2);

            $table->decimal("total_cost", 10, 2);
            $table->decimal("net_profit", 10, 2);

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
