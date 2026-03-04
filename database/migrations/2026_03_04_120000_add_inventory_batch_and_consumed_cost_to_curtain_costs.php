<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curtain_costs', function (Blueprint $table) {
            $table->foreignId('inventory_batch_id')
                ->nullable()
                ->after('product_color_id')
                ->constrained('inventory_batches')
                ->nullOnDelete();
            $table->decimal('consumed_cost', 12, 2)->nullable()->after('quantity')->comment('تكلفة من الدفعة عند الاستهلاك');
        });
    }

    public function down(): void
    {
        Schema::table('curtain_costs', function (Blueprint $table) {
            $table->dropForeign(['inventory_batch_id']);
            $table->dropColumn('consumed_cost');
        });
    }
};
