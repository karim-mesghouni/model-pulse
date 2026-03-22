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
        Schema::create('model_pulse_activity_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the plan');
            $table->boolean('is_active')->nullable()->default(false)->comment('Status');

             $table->morphs("creator");

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pulse_activity_plans');
    }
};
