<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'إجازة سنوية', 'days' => 14, 'paid' => true],
            ['name' => 'إجازة مرضية', 'days' => 14, 'paid' => true],
            ['name' => 'إجازة طارئة', 'days' => 3, 'paid' => true],
            ['name' => 'إجازة بدون راتب', 'days' => 0, 'paid' => false],
        ];

        foreach ($types as $t) {
            LeaveType::firstOrCreate(
                ['name' => $t['name']],
                ['default_days_per_year' => $t['days'], 'is_paid' => $t['paid']]
            );
        }
    }
}
