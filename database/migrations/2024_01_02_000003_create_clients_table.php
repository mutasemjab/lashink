<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 30)->unique();
            $table->string('phone2', 30)->nullable();
            $table->string('gender', 10)->default('female');
            $table->date('birthdate')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('source', 100)->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
