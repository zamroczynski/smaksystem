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
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('product_type_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('unit_of_measure_id')->index();
            $table->unsignedBigInteger('vat_rate_id')->index();

            $table->boolean('is_sellable')->default(false);
            $table->boolean('is_inventoried')->default(true);

            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('default_purchase_price', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
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
