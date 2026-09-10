<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'رموش' => [
                ['name' => 'تركيب رموش كلاسيك', 'duration' => 90, 'price' => 20, 'commission' => 20],
                ['name' => 'تركيب رموش فوليوم', 'duration' => 120, 'price' => 30, 'commission' => 20],
                ['name' => 'تعبئة رموش', 'duration' => 60, 'price' => 15, 'commission' => 20],
            ],
            'أظافر' => [
                ['name' => 'مناكير جل', 'duration' => 45, 'price' => 12, 'commission' => 15],
                ['name' => 'بديكير', 'duration' => 45, 'price' => 12, 'commission' => 15],
                ['name' => 'تركيب أظافر أكريليك', 'duration' => 90, 'price' => 25, 'commission' => 15],
            ],
            'شعر' => [
                ['name' => 'قص وتصفيف', 'duration' => 60, 'price' => 15, 'commission' => 10],
                ['name' => 'صبغة شعر', 'duration' => 120, 'price' => 35, 'commission' => 10],
                ['name' => 'بروتين شعر', 'duration' => 150, 'price' => 50, 'commission' => 10],
            ],
            'بشرة' => [
                ['name' => 'تنظيف بشرة عميق', 'duration' => 60, 'price' => 25, 'commission' => 10],
                ['name' => 'جلسة هيدرافيشل', 'duration' => 60, 'price' => 40, 'commission' => 10],
            ],
            'مكياج' => [
                ['name' => 'مكياج سهرة', 'duration' => 60, 'price' => 30, 'commission' => 25],
                ['name' => 'مكياج عروس', 'duration' => 120, 'price' => 80, 'commission' => 25],
            ],
        ];

        foreach ($categories as $catName => $services) {
            $category = ServiceCategory::firstOrCreate(['name' => $catName]);

            foreach ($services as $s) {
                Service::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $s['name']],
                    [
                        'duration_minutes'  => $s['duration'],
                        'price'             => $s['price'],
                        'commission_type'   => 'percent',
                        'commission_value'  => $s['commission'],
                        'is_active'         => true,
                    ]
                );
            }
        }
    }
}
