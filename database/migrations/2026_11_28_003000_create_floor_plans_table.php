<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floor_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id');
            $table->foreignId('floor_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->softDeletes();
            $table->timestamps();

            $table->index('building_id');
            $table->index('floor_id');
            $table->foreign('building_id')->references('id')->on('buildings')->restrictOnDelete();
            $table->foreign('floor_id')->references('id')->on('floors')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_plans');
    }
};
