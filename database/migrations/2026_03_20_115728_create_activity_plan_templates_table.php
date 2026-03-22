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
        Schema::create('model_pulse_activity_plan_templates', function (Blueprint $table) {
            $table->id();

            $table->integer('sort')->nullable()->comment('Sort Order');
            $table->foreignId('plan_id')->constrained("model_pulse_activity_plans")->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained("model_pulse_activity_types")->restrictOnDelete();
            $table->morphs("creator");
            $table->morphs("responsible");
            $table->morphs("assignable");
            $table->string('supervisor_type')->comment('Supervisor Type');

            $table->integer('delay_count')->nullable()->comment('Delay count');
            $table->string('delay_unit')->comment('Delay unit');
            $table->string('delay_from')->comment('Delay From');
            $table->text('summary')->nullable()->comment('Summary');
            $table->text('note')->nullable()->comment('Note');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pulse_activity_plan_templates');
    }
};
