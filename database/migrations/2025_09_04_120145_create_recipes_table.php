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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            $table->foreignId('product_id')
                ->comment('The final product that this recipe creates')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->decimal('yield_quantity', 10, 4)->default(1.0000);

            $table->foreignId('yield_unit_of_measure_id')
                ->constrained('unit_of_measures')
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
