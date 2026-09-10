<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('name', 200);
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('commission_type', 10)->default('percent'); // percent | fixed
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['service_id', 'admin_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_employee');
        Schema::dropIfExists('services');
    }
};
