<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['working', 'not_working', 'at_risk', 'needs_scaling', 'recommendations']);
            $table->string('title');
            $table->text('content');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
