<?php

return [
    'bundle_id' => env('APPLE_IAP_BUNDLE_ID', 'com.baheth.school'),
    'course_product_prefix' => env(
        'APPLE_IAP_COURSE_PRODUCT_PREFIX',
        'com.baheth.school.course.v2.'
    ),
    'course_product_type' => 'Non-Consumable',

    // App Review purchases are signed for Sandbox even when the submitted
    // binary is a release build. Production must therefore accept and record
    // both environments, while still rejecting every other environment.
    'allowed_environments' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('APPLE_IAP_ALLOWED_ENVIRONMENTS', 'Production,Sandbox'))
    ))),

    'max_clock_skew_seconds' => (int) env('APPLE_IAP_MAX_CLOCK_SKEW_SECONDS', 300),

    // These are Apple's public roots, committed from Apple PKI. Never trust a
    // root supplied only in the transaction's x5c header.
    'trusted_root_certificates' => [
        resource_path('certificates/apple/AppleIncRootCertificate.pem'),
        resource_path('certificates/apple/AppleRootCA-G2.pem'),
        resource_path('certificates/apple/AppleRootCA-G3.pem'),
    ],
];
