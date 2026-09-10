<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('national_id', 30)->nullable()->after('phone');
            $table->date('hire_date')->nullable()->after('national_id');
            $table->decimal('base_salary', 10, 2)->default(0)->after('hire_date');
            $table->decimal('commission_percent', 5, 2)->default(0)->after('base_salary');
            $table->string('employment_status', 20)->default('active')->after('commission_percent');
            $table->text('address')->nullable()->after('employment_status');
            $table->string('photo')->nullable()->after('address');
            $table->text('notes')->nullable()->after('photo');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'national_id', 'hire_date', 'base_salary',
                'commission_percent', 'employment_status', 'address', 'photo', 'notes',
            ]);
        });
    }
};
