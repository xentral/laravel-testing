<?php declare(strict_types=1);

return [
    'behat' => [
        'matcher_lookup_path' => base_path('tests'),
    ],
    'openapi' => [
        'default_schema' => base_path('openapi.yaml'),
        'schema_base_path' => base_path('tests/fixtures'),
    ],
];
