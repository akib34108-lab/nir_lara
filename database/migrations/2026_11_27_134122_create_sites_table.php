<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->tinyInteger('project_status')->default(1)->comment('1 = Planning, 2 = Active, 3 = On Hold, 4 = Completed, 5 = Cancelled');
            $table->text('description')->nullable();

            // Location Information
            $table->string('address');
            $table->string('area')->nullable();
            $table->string('city');
            $table->string('district');
            $table->string('division')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('postal_code')->nullable();
            $table->string('google_maps_location')->nullable();

            // Land Information
            $table->decimal('land_area', 12, 2);
            $table->tinyInteger('land_area_unit')->default(1)->comment('1 = Katha, 2 = Decimal, 3 = Acre, 4 = Bigha, 5 = Square Feet, 6 = Square Meter');
            $table->string('plot_number')->nullable();
            $table->string('block')->nullable();
            $table->string('road_number')->nullable();
            $table->string('sector')->nullable();
            $table->text('land_description')->nullable();

            // Project Timeline
            $table->date('planned_start_date')->nullable();
            $table->date('planned_completion_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            // Share Information
            $table->unsignedInteger('total_shares')->nullable();
            $table->decimal('per_share_value', 15, 2)->nullable();

            // Budget Information
            $table->decimal('estimated_project_budget', 15, 2)->nullable();

            // System Fields
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
