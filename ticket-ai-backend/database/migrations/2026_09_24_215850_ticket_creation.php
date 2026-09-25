<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('body');

            // Preenchidos pelo LLM
            $table->string('category')->nullable()->index();
            $table->string('priority')->nullable()->index();
            $table->string('sentiment')->nullable();
            $table->text('summary')->nullable();

            $table->timestamp('analyzed_at')->nullable();
            $table->text('analysis_error')->nullable();
            $table->json('embedding')->nullable()->after('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
