<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->restrictOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('building_type', 100);
            $table->string('structure_type', 100);
            $table->unsignedInteger('total_floors')->default(0);
            $table->unsignedInteger('total_flats')->default(0);
            $table->unsignedInteger('basement_count')->default(0);
            $table->unsignedInteger('elevator_count')->default(0);
            $table->unsignedInteger('parking_capacity')->default(0);
            $table->string('status', 32)->default('planning');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
