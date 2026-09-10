<?php

namespace Tests\Feature;

use App\Contracts\AppleTransactionVerifier;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\Apple\ApplePurchaseToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppleInAppPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_returns_a_permanent_app_account_token(): void
    {
        $response = $this->postJson('/api/v1/student/auth/register', [
            'name' => 'Review Student',
            'national_id' => '0790000000',
            'phone' => '0790000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'deviceId' => '98fd3b78-e6df-489c-9a93-5ff830e66900',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['token', 'student' => ['app_account_token']]]);

        $token = $response->json('data.student.app_account_token');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $token
        );
        $this->assertDatabaseHas('students', ['app_account_token' => $token]);
    }

    public function test_verified_purchase_is_idempotent_and_grants_the_selected_course(): void
    {
        $student = $this->student();
        $course = $this->paidCourse();
        $purchaseToken = (new ApplePurchaseToken())->forCourse(
            $student->app_account_token,
            $course->id
        );
        $productId = 'com.baheth.school.course.v2.'.$course->id;

        $this->app->instance(
            AppleTransactionVerifier::class,
            new FakeAppleTransactionVerifier($purchaseToken, $productId)
        );
        Sanctum::actingAs($student);

        $payload = [
            'course_id' => $course->id,
            'product_id' => $productId,
            'transaction_id' => '200000000000001',
            'signed_transaction' => 'header.payload.signature',
            'purchase_token' => $purchaseToken,
            'source' => 'app_store',
        ];

        $this->postJson('/api/v1/student/purchases/apple/verify', $payload)
            ->assertOk()
            ->assertJsonPath('data.is_enrolled', true)
            ->assertJsonPath('data.course_id', $course->id);

        $student->enrollments()->where('course_id', $course->id)->update([
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/student/purchases/apple/verify', $payload)
            ->assertOk()
            ->assertJsonPath('data.transaction_id', '200000000000001');

        $this->assertDatabaseCount('apple_purchases', 1);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
    }

    public function test_purchase_is_rejected_when_the_signed_token_does_not_match_the_course(): void
    {
        $student = $this->student();
        $course = $this->paidCourse();
        $courseToken = (new ApplePurchaseToken())->forCourse(
            $student->app_account_token,
            $course->id
        );
        $differentCourseToken = (new ApplePurchaseToken())->forCourse(
            $student->app_account_token,
            $course->id + 1
        );

        $this->app->instance(
            AppleTransactionVerifier::class,
            new FakeAppleTransactionVerifier(
                $differentCourseToken,
                'com.baheth.school.course.v2.'.$course->id
            )
        );
        Sanctum::actingAs($student);

        $this->postJson('/api/v1/student/purchases/apple/verify', [
            'course_id' => $course->id,
            'product_id' => 'com.baheth.school.course.v2.'.$course->id,
            'transaction_id' => '200000000000001',
            'signed_transaction' => 'header.payload.signature',
            'purchase_token' => $courseToken,
        ])->assertStatus(422);

        $this->assertDatabaseCount('apple_purchases', 0);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_purchase_is_rejected_when_the_product_does_not_match_the_course(): void
    {
        $student = $this->student();
        $course = $this->paidCourse();
        $purchaseToken = (new ApplePurchaseToken())->forCourse(
            $student->app_account_token,
            $course->id
        );

        $this->app->instance(
            AppleTransactionVerifier::class,
            new FakeAppleTransactionVerifier(
                $purchaseToken,
                'com.baheth.school.course.v2.'.($course->id + 1)
            )
        );
        Sanctum::actingAs($student);

        $this->postJson('/api/v1/student/purchases/apple/verify', [
            'course_id' => $course->id,
            'product_id' => 'com.baheth.school.course.v2.'.($course->id + 1),
            'transaction_id' => '200000000000001',
            'signed_transaction' => 'header.payload.signature',
            'purchase_token' => $purchaseToken,
        ])->assertStatus(422);

        $this->assertDatabaseCount('apple_purchases', 0);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_a_transaction_cannot_be_replayed_for_another_student(): void
    {
        $firstStudent = $this->student();
        $secondStudent = Student::create([
            'name' => 'Second Student',
            'national_id' => '0790000001',
            'phone' => '0790000001',
            'password' => 'password123',
            'deviceId' => '1e32c743-b648-4a0a-92e3-485d139d1e14',
            'is_active' => true,
        ]);
        $course = $this->paidCourse();

        $firstToken = (new ApplePurchaseToken())->forCourse(
            $firstStudent->app_account_token,
            $course->id
        );
        $productId = 'com.baheth.school.course.v2.'.$course->id;
        $verifier = new FakeAppleTransactionVerifier($firstToken, $productId);
        $this->app->instance(AppleTransactionVerifier::class, $verifier);
        Sanctum::actingAs($firstStudent);

        $this->postJson('/api/v1/student/purchases/apple/verify', [
            'course_id' => $course->id,
            'product_id' => $productId,
            'transaction_id' => '200000000000001',
            'signed_transaction' => 'header.payload.signature',
            'purchase_token' => $firstToken,
        ])->assertOk();

        $secondToken = (new ApplePurchaseToken())->forCourse(
            $secondStudent->app_account_token,
            $course->id
        );
        $verifier->purchaseToken = $secondToken;
        Sanctum::actingAs($secondStudent);

        $this->postJson('/api/v1/student/purchases/apple/verify', [
            'course_id' => $course->id,
            'product_id' => $productId,
            'transaction_id' => '200000000000001',
            'signed_transaction' => 'header.payload.signature',
            'purchase_token' => $secondToken,
        ])->assertStatus(409);

        $this->assertDatabaseCount('apple_purchases', 1);
        $this->assertDatabaseHas('apple_purchases', [
            'student_id' => $firstStudent->id,
            'course_id' => $course->id,
        ]);
        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $secondStudent->id,
            'course_id' => $course->id,
        ]);
    }

    private function student(): Student
    {
        return Student::create([
            'name' => 'Review Student',
            'national_id' => '0790000000',
            'phone' => '0790000000',
            'password' => 'password123',
            'deviceId' => '98fd3b78-e6df-489c-9a93-5ff830e66900',
            'is_active' => true,
        ]);
    }

    private function paidCourse(): Course
    {
        $teacher = Teacher::create([
            'name' => 'Teacher',
            'national_id' => 'teacher-1',
            'password' => 'password123',
            'is_active' => true,
        ]);

        return Course::create([
            'teacher_id' => $teacher->id,
            'title_ar' => 'دورة مراجعة Apple',
            'price' => 1.99,
            'is_free' => false,
            'is_published' => true,
        ]);
    }
}

class FakeAppleTransactionVerifier implements AppleTransactionVerifier
{
    public function __construct(
        public string $purchaseToken,
        public string $productId
    ) {
    }

    public function verify(string $signedTransaction): array
    {
        $now = (int) floor(microtime(true) * 1000);

        return [
            'bundle_id' => 'com.baheth.school',
            'product_id' => $this->productId,
            'transaction_id' => '200000000000001',
            'original_transaction_id' => '200000000000001',
            'app_account_token' => $this->purchaseToken,
            'environment' => 'Sandbox',
            'type' => 'Non-Consumable',
            'quantity' => 1,
            'purchase_date_ms' => $now,
            'signed_date_ms' => $now,
            'payload' => ['test' => true],
        ];
    }
}
