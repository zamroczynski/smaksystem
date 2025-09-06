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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipe_id') 
                  ->constrained('recipes')
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->comment('The ingredient product')
                  ->constrained('products')
                  ->cascadeOnDelete();
            
            $table->decimal('quantity', 10, 4);

            $table->foreignId('unit_of_measure_id')
                  ->constrained('unit_of_measures')
                  ->restrictOnDelete();

            $table->unique(['recipe_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
