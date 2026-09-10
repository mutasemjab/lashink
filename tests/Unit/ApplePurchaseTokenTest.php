<?php

namespace Tests\Unit;

use App\Exceptions\ApplePurchaseException;
use App\Services\Apple\ApplePurchaseToken;
use PHPUnit\Framework\TestCase;

class ApplePurchaseTokenTest extends TestCase
{
    public function test_it_matches_the_flutter_purchase_token_algorithm(): void
    {
        $token = (new ApplePurchaseToken())->forCourse(
            '5a1b23af-bf50-4afd-9a3a-c643f32c7f81',
            42
        );

        $this->assertSame('2c6cdbad-2b37-8e8d-927d-68260000002a', $token);
    }

    public function test_it_rejects_invalid_student_tokens_and_course_ids(): void
    {
        $tokens = new ApplePurchaseToken();

        $this->expectException(ApplePurchaseException::class);
        $tokens->forCourse('not-a-uuid', 42);
    }

    public function test_it_binds_different_courses_to_different_tokens(): void
    {
        $tokens = new ApplePurchaseToken();
        $studentToken = '5a1b23af-bf50-4afd-9a3a-c643f32c7f81';

        $this->assertNotSame(
            $tokens->forCourse($studentToken, 1),
            $tokens->forCourse($studentToken, 2)
        );
    }
}
