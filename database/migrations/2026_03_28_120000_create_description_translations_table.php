<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('description_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_description_id')->constrained()->cascadeOnDelete();
            $table->string('target_language', 10);
            $table->string('source_language', 10)->nullable();
            $table->string('provider', 50)->default('deepl');
            $table->string('status', 20)->default('pending');
            $table->longText('translated_text')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('billed_characters')->nullable();
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['generated_description_id', 'target_language'], 'desc_translations_desc_lang_unique');
            $table->index('status', 'desc_translations_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('description_translations');
    }
};
