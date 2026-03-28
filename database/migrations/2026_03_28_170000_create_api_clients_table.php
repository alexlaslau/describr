<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status'], 'api_clients_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
