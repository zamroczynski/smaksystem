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
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('position')->default(1)->after('assignment_date');
            $table->unique(['schedule_id', 'shift_template_id', 'assignment_date', 'position'], 'unique_assignment_per_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropUnique('unique_assignment_per_position');
            $table->dropColumn('position');
        });
    }
};
