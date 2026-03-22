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
        Schema::create('model_pulse_attachments', function (Blueprint $table) {
            $table->id();

            $table->morphs("creator");
            $table->foreignId('message_id')->nullable()->comment('Message')->references('id')->on('model_pulse_messages')->cascadeOnDelete();
            $table->string('file_size')->nullable()->comment('File Size');
            $table->string('name')->nullable()->comment('Name');
            $table->morphs('messageable');
            $table->string('file_path')->nullable()->comment('File Path');
            $table->string('original_file_name')->nullable()->comment('Original File Name');
            $table->string('mime_type')->nullable()->comment('Mime Type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pulse_attachments');
    }
};
