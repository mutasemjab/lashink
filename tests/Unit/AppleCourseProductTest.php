<?php

namespace Tests\Unit;

use App\Exceptions\ApplePurchaseException;
use App\Services\Apple\AppleCourseProduct;
use Tests\TestCase;

class AppleCourseProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'apple_iap.course_product_prefix' => 'com.baheth.school.course.v2.',
        ]);
    }

    public function test_it_round_trips_course_product_ids(): void
    {
        $products = new AppleCourseProduct();

        $this->assertSame('com.baheth.school.course.v2.42', $products->forCourse(42));
        $this->assertSame(42, $products->courseIdFrom('com.baheth.school.course.v2.42'));
    }

    public function test_it_rejects_malformed_product_ids(): void
    {
        $products = new AppleCourseProduct();

        $this->assertNull($products->courseIdFrom('com.baheth.school.course.access'));
        $this->assertNull($products->courseIdFrom('com.baheth.school.course.v2.0'));
        $this->assertNull($products->courseIdFrom('com.baheth.school.course.v2.01'));
    }

    public function test_it_rejects_invalid_course_ids(): void
    {
        $this->expectException(ApplePurchaseException::class);

        (new AppleCourseProduct())->forCourse(0);
    }
}
