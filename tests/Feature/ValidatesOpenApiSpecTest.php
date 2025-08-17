<?php declare(strict_types=1);

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use Workbench\App\Models\TestModel;
use Xentral\LaravelTesting\OpenApi\ValidatesOpenApiSpec;

uses(ValidatesOpenApiSpec::class);

beforeEach(function () {
    $this->schemaFilePath(dirname(__DIR__).'/schemas/test-models.json');
});

test('endpoint returns valid response with empty data', function () {
    $response = $this->getJson('/api/v1/test-models');

    $response->assertOk();
    $response->assertJson(['data' => []]);
});

test('invalid call expects exception', function () {
    $this->expectException(NoPath::class);
    $this->getJson('/api/v1/test-modelss');
});

test('endpoint returns valid response with test data', function () {
    // Create test models using factory
    $testModels = TestModel::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/test-models');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');

    // Verify structure contains expected fields
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ],
        ],
    ]);

    // Verify actual data matches what we created
    $responseData = $response->json('data');
    foreach ($testModels as $index => $model) {
        expect($responseData[$index])
            ->toHaveKeys(['id', 'name', 'slug', 'created_at', 'updated_at'])
            ->and($responseData[$index]['id'])->toBe($model->id)
            ->and($responseData[$index]['name'])->toBe($model->name)
            ->and($responseData[$index]['slug'])->toBe($model->slug);
    }
});

test('endpoint response validates timestamps format', function () {
    $this->freezeSecond(function () {
        $now = now();
        TestModel::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/test-models');
        $response->assertJsonPath('data.0.created_at', $now->toISOString());
    });
});
