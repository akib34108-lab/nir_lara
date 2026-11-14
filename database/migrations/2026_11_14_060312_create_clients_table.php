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
    Schema::create('clients', function (Blueprint $table) {
        $table->id();

        // Client Information
        $table->string('client_code')->unique();
        $table->tinyInteger('client_type')->default(1)->comment('1 = Individual, 2 = Company, 3 = Government, 4 = Organization');
        $table->string('name');
        $table->string('company_name')->nullable();

        // Contact Information
        $table->string('email')->nullable();
        $table->string('phone', 20);
        $table->string('alternate_phone', 20)->nullable();
        $table->string('website')->nullable();

        // Business Information
        $table->string('tax_number')->nullable();

        // Address Information
        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();
        $table->string('country')->default('Bangladesh');
        $table->string('postal_code', 20)->nullable();

        // Additional Information
        $table->text('notes')->nullable();
        $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Inactive, 3 = Pending, 4 = Blocked');

        $table->timestamps();
        $table->softDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
