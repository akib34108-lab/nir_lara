<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->restrictOnDelete();
            $table->string('name');
            $table->smallInteger('floor_number');
            $table->string('floor_type', 32);
            $table->decimal('floor_area', 12, 2)->nullable();
            $table->string('floor_area_unit', 8)->default('sqft');
            $table->decimal('common_area', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('configuration')->nullable();
            $table->string('status', 32)->default('planned');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['building_id', 'floor_number']);
            $table->unique(['building_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
