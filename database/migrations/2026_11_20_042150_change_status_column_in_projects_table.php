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
        Schema::table('projects', function (Blueprint $table) {
            $table
            ->tinyInteger('status')
            ->default(1)
            ->comment('1 = Planning, 2 = Pending, 3 = Acitve, 4 = On Hold, 5 = Completed, 6 = Cancelled')
            ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 50)->default('planning')->change();
        });
    }
};
