<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Karim\ModelPulse\Enums\ActivityChainingType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_pulse_activity_types', function (Blueprint $table) {
            $table->id();

            $table->integer('sort')->nullable()->comment('Sort order');
            $table->integer('delay_count')->nullable()->comment('Delay count');
            $table->string('delay_unit')->comment('Delay unit');
            $table->string('delay_from')->comment('Delay from');
            $table->string('icon')->nullable()->comment('Icon');
            $table->string('decoration_type')->nullable()->comment('Decoration type');
            $table->string('chaining_type')->nullable()->default(ActivityChainingType::SUGGEST)->comment('Chaining type');
            $table->string('plugin')->nullable()->comment('Plugin name');
            $table->string('category')->nullable()->comment('Category');
            $table->string('name')->comment('Name');
            $table->text('summary')->nullable()->comment('Summary');
            $table->text('default_note')->nullable()->comment('Default Note');
            $table->boolean('is_active')->default(true)->comment('Status');
            $table->boolean('keep_done')->default(false)->comment('Keep Done');
            $table->morphs("creator");

            $table->unsignedBigInteger('default_user_id')->nullable()->comment('Default User');
            $table->foreignId('activity_plan_id')->nullable()->constrained('model_pulse_activity_plans')->cascadeOnDelete();
            $table->foreignId('triggered_next_type_id')->nullable()->constrained('model_pulse_activity_types')->restrictOnDelete();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pulse_activity_types');
    }
};
