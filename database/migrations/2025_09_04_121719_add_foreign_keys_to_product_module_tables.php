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
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('unit_of_measure_id')->references('id')->on('unit_of_measures')->onDelete('cascade');
            $table->foreign('vat_rate_id')->references('id')->on('vat_rates')->onDelete('cascade');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });

        Schema::table('product_supplier', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->foreign('dish_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('ingredient_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['dish_product_id']);
            $table->dropForeign(['ingredient_product_id']);
        });

        Schema::table('product_supplier', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropForeign(['vat_rate_id']);
        });
    }
};
