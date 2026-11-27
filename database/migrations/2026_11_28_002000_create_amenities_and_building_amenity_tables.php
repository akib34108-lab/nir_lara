<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamps();

            $table->index(['category', 'status']);
        });

        Schema::create('building_amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['building_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_amenity');
        Schema::dropIfExists('amenities');
    }
};
