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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('refund_number')->unique();

            $table->float('total_refund_amount', 10, 2);

            $table->enum('refund_method', ['cash', 'card', 'store_credit']);

            $table->text('reason')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
