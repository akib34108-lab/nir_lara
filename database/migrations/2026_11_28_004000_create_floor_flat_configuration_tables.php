<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('floors', 'configuration')) {
            Schema::table('floors', function (Blueprint $table) {
                $table->json('configuration')->nullable();
            });
        }

        Schema::create('floor_flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained('floors')->restrictOnDelete();
            $table->string('flat_number');
            $table->string('name')->nullable();
            $table->string('flat_type', 32)->nullable();
            $table->decimal('area', 12, 2)->nullable();
            $table->string('area_unit', 8)->default('sqft');
            $table->unsignedInteger('bedroom_count')->default(0);
            $table->unsignedInteger('master_bedroom_count')->default(0);
            $table->unsignedInteger('bathroom_count')->default(0);
            $table->unsignedInteger('balcony_count')->default(0);
            $table->unsignedInteger('kitchen_count')->default(0);
            $table->unsignedInteger('living_room_count')->default(0);
            $table->unsignedInteger('dining_room_count')->default(0);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('planned');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['floor_id', 'flat_number']);
        });

        Schema::create('floor_flat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flat_id')->constrained('floor_flats')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('room_type', 32);
            $table->decimal('length', 12, 2)->nullable();
            $table->decimal('width', 12, 2)->nullable();
            $table->decimal('area', 12, 2)->nullable();
            $table->string('area_unit', 8)->default('sqft');
            $table->string('floor_level')->nullable();
            $table->boolean('attached_bathroom')->default(false);
            $table->unsignedInteger('door_count')->default(0);
            $table->unsignedInteger('window_count')->default(0);
            $table->string('flooring_type')->nullable();
            $table->string('ceiling_type')->nullable();
            $table->string('wall_finish')->nullable();
            $table->string('ac_provision', 32)->nullable();
            $table->string('electrical_provision', 32)->nullable();
            $table->string('plumbing_provision', 32)->nullable();
            $table->text('ventilation_details')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('properties')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['flat_id', 'room_type']);
        });

        Schema::create('floor_common_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained('floors')->restrictOnDelete();
            $table->string('name');
            $table->string('area_type', 32);
            $table->decimal('length', 12, 2)->nullable();
            $table->decimal('width', 12, 2)->nullable();
            $table->decimal('area', 12, 2)->nullable();
            $table->string('area_unit', 8)->default('sqft');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['floor_id', 'area_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_common_areas');
        Schema::dropIfExists('floor_flat_rooms');
        Schema::dropIfExists('floor_flats');

        if (Schema::hasColumn('floors', 'configuration')) {
            Schema::table('floors', function (Blueprint $table) {
                $table->dropColumn('configuration');
            });
        }
    }
};
