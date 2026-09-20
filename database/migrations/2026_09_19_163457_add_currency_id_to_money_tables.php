<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['invoices', 'expenses', 'products', 'services', 'purchases', 'salary_advances', 'admins', 'payroll_items'];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('currency_id')->nullable()->after('id')->constrained('currencies')->nullOnDelete();
            });
        }

        $defaultId = DB::table('currencies')->where('is_default', true)->value('id');
        foreach ($this->tables as $table) {
            DB::table($table)->whereNull('currency_id')->update(['currency_id' => $defaultId]);
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('currency_id');
            });
        }
    }
};
