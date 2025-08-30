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
        Schema::create('holiday_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_definition_id')->constrained('holidays')->onDelete('cascade');
            $table->string('name', 100);
            $table->date('date');
            $table->unique(['date', 'holiday_definition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_instances');
    }
};
