<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['إيجار', 'فواتير وخدمات', 'رواتب', 'مشتريات مخزون', 'تسويق وإعلان', 'صيانة', 'أخرى'];

        foreach ($names as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
