<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Inactive');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_code', 50)->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('material_categories')->restrictOnDelete();
            $table->string('unit', 50);
            $table->text('description')->nullable();
            $table->decimal('minimum_stock_level', 15, 3)->default(0);
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Inactive');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->unique();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Bangladesh');
            $table->string('tax_number', 100)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Inactive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('material_categories');
    }
};
