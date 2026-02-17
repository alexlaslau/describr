<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->longText('raw_html')->nullable();
            $table->enum('status', ['pending', 'scraping', 'scraped', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_links');
    }
};