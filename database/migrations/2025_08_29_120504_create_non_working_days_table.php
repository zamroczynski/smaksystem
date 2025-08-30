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
        Schema::create('non_working_days', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('date')->nullable()->comment('Full date for single days off.');
            $table->string('day_month', 5)->nullable()->comment("Format 'DD-MM' for annual holidays with a fixed date.");
            $table->json('calculation_rule')->nullable()->comment('Calculation rule for movable holidays, like {"base": "easter", "offset": 60}.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_working_days');
    }
};
