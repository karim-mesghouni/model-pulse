<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up() {

        Schema::create("model_pulse_messages", function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_type_id')->nullable()->references("id")->on("model_pulse_activity_types")->cascadeOnDelete();
            $table->morphs("assignable");
            $table->morphs('messageable');
            $table->string('type')->nullable()->comment('Message Type');
            $table->string('name')->nullable()->comment('Name');
            $table->string('subject')->nullable()->comment('Subject');
            $table->text('body')->nullable()->comment('Body');
            $table->text('summary')->nullable()->comment('Summary');
            $table->boolean('is_internal')->nullable()->comment('Is Internal');
            $table->date('date_deadline')->nullable()->comment('Date');
            $table->date('pinned_at')->nullable()->comment('Pinned At');
            $table->string('log_name')->nullable();
            $table->morphs('causer');
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->boolean('is_read')->default(0);

            $table->timestamps();

        });
    }

    public function down() {

        Schema::dropIfExists("model_pulse_messages");

    }

};