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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId("category_id")->constrained();

            $table->string("name");
            $table->enum("unit", ["piece", "meter"]); // (piece | meter)

            $table->decimal("stock", 10, 2)->default(0);
            $table->decimal("cost_price", 10, 2);
            $table->decimal("selling_price", 10, 2);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
