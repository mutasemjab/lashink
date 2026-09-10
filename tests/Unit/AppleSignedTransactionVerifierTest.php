<?php

namespace Tests\Unit;

use App\Exceptions\ApplePurchaseException;
use App\Services\Apple\AppleSignedTransactionVerifier;
use Tests\TestCase;

class AppleSignedTransactionVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'apple_iap.bundle_id' => 'com.baheth.school',
            // The cryptographic fixture predates the production switch to
            // per-course non-consumables. Override only the expected type so
            // the fixture can continue exercising signature verification.
            'apple_iap.course_product_type' => 'Consumable',
            'apple_iap.allowed_environments' => ['Production', 'Sandbox'],
            'apple_iap.trusted_root_certificates' => [
                base_path('tests/Fixtures/apple/TestRoot.pem'),
            ],
        ]);
    }

    public function test_it_verifies_a_valid_storekit_style_signed_transaction(): void
    {
        $verified = (new AppleSignedTransactionVerifier())->verify($this->fixture());

        $this->assertSame('com.baheth.school', $verified['bundle_id']);
        $this->assertSame('com.baheth.school.course.access', $verified['product_id']);
        $this->assertSame('200000000099999', $verified['transaction_id']);
        $this->assertSame('2c6cdbad-2b37-8e8d-927d-68260000002a', $verified['app_account_token']);
        $this->assertSame('Sandbox', $verified['environment']);
        $this->assertSame('Consumable', $verified['type']);
        $this->assertSame(1, $verified['quantity']);
    }

    public function test_it_rejects_a_transaction_with_a_tampered_payload(): void
    {
        [$header, $payload, $signature] = explode('.', $this->fixture());
        $decoded = json_decode($this->base64UrlDecode($payload), true, flags: JSON_THROW_ON_ERROR);
        $decoded['productId'] = 'com.attacker.fake-product';
        $tamperedPayload = $this->base64UrlEncode(json_encode($decoded, JSON_THROW_ON_ERROR));

        $this->expectException(ApplePurchaseException::class);

        (new AppleSignedTransactionVerifier())->verify(
            $header.'.'.$tamperedPayload.'.'.$signature
        );
    }

    public function test_production_product_type_rejects_a_consumable_transaction(): void
    {
        config(['apple_iap.course_product_type' => 'Non-Consumable']);

        $this->expectException(ApplePurchaseException::class);

        (new AppleSignedTransactionVerifier())->verify($this->fixture());
    }

    public function test_it_rejects_a_certificate_chain_not_anchored_to_a_trusted_root(): void
    {
        config([
            'apple_iap.trusted_root_certificates' => [
                resource_path('certificates/apple/AppleRootCA-G3.pem'),
            ],
        ]);

        $this->expectException(ApplePurchaseException::class);

        (new AppleSignedTransactionVerifier())->verify($this->fixture());
    }

    private function fixture(): string
    {
        return trim((string) file_get_contents(
            base_path('tests/Fixtures/apple/valid-transaction.jws')
        ));
    }

    private function base64UrlDecode(string $value): string
    {
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);

        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
