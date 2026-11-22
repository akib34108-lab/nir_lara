<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_inventories', function (Blueprint $table) {
            $table->decimal('reserved_quantity', 15, 3)->default(0)->after('quantity');
            $table->decimal('average_cost', 15, 2)->default(0)->after('reserved_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('material_inventories', function (Blueprint $table) {
            $table->dropColumn(['reserved_quantity', 'average_cost']);
        });
    }
};
