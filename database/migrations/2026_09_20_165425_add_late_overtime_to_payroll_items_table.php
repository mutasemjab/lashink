<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('late_deduction', 10, 2)->default(0)->after('advance_deduction');
            $table->decimal('overtime_amount', 10, 2)->default(0)->after('commission_amount');
        });
    }

    public function down()
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['late_deduction', 'overtime_amount']);
        });
    }
};
