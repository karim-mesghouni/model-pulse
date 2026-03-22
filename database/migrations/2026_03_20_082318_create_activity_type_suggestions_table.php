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
        Schema::create('model_pulse_activity_type_suggestions', function (Blueprint $table) {
            $table->foreignId('activity_type_id')->references("id")->on("model_pulse_activity_types")->cascadeOnDelete();
            $table->foreignId('suggested_activity_type_id')->references("id")->on("model_pulse_activity_types")->cascadeOnDelete();
            $table->primary(['activity_type_id', 'suggested_activity_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pulse_activity_type_suggestions');
    }
};
