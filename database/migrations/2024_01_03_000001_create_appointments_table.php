<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('admins')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending|confirmed|completed|cancelled|no_show
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->text('notes')->nullable();
            $table->string('cancelled_reason', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['start_at', 'end_at']);
        });

        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_services');
        Schema::dropIfExists('appointments');
    }
};
