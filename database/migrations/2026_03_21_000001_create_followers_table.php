<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up() {

        Schema::create("model_pulse_followers", function (Blueprint $table) {
            $table->id();
            $table->morphs('followable');
            $table->morphs('follower');
            $table->timestamp('followed_at')->nullable();
            $table->timestamps();

            $table->unique(['followable_type', 'followable_id', 'follower_id' , "follower_type"], 'model_pulse_followers_unique');

        });
    }

    public function down() {

        Schema::dropIfExists("model_pulse_followers");

    }

};