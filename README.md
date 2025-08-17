# Laravel Testing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/xentral/laravel-testing.svg?style=flat-square)](https://packagist.org/packages/xentral/laravel-testing)
[![Total Downloads](https://img.shields.io/packagist/dt/xentral/laravel-testing.svg?style=flat-square)](https://packagist.org/packages/xentral/laravel-testing)
![GitHub Actions](https://github.com/xentral/laravel-testing/actions/workflows/main.yml/badge.svg)

This package provides comprehensive testing tools for Laravel applications, including OpenAPI validation, Behat feature
testing, and Qase test reporting integration.

## Installation

You can install the package via composer.

```bash
composer --dev require xentral/laravel-testing
```

## Usage

### OpenAPI Validation

Automatically validate your API responses against OpenAPI schemas in your tests.

The Schema file will be determined in the following order:

* Using the `schemaFilePath` property (if not `null`)
* Using the `SchemaFile` attribute
* Using the configured `testing.openapi.default_schema` config value

<info>Relative paths will be auto prefixed with the project base path (by default)</i>

**Basic example with PHPUnit:**

```php
<?php

#[SchemaFile('api-schema.yml')]
class MyEndpointTest extendsTestCase
{
    use \Xentral\LaravelTesting\OpenApi\ValidatesOpenApiSpec;
    
    public function test_api_returns_valid_response()
    {
        $response = $this->getJson('/api/users')->assertOk();
    }
}
```

**Basic example with Pest:**
Pest does not support attributes (at least i couldn't figure it out). You need to use beforeEach to set the schema file
path.

```php
<?php

uses(Xentral\LaravelTesting\OpenApi\ValidatesOpenApiSpec::class);

beforeEach(function () {
    $this->schemaFilePath('api-schema.yml');
});

test('API returns valid response', function () {
    $response = $this->getJson('/api/users')->assertOk(); // Automatically validates against OpenAPI schema
});
```

### Behat Feature Testing

Write behavior-driven tests using Gherkin syntax alongside your PHPUnit tests. (Does not work with Pest yet)

**Basic Usage:**

```php
// tests/Feature/MyEndpointTest.php
<?php

class MyEndpointTest extends TestCase
{
    use HasBehatFeature;

    #[DataProvider('featureProvider')]
    public function test_behat_scenario($scenario, $feature)
    {
        $this->executeScenario($scenario, $feature);
    }
}
```

**Feature File:**

```gherkin
# tests/Feature/MyEndpointTest.feature
Feature: Basic example feature

  Scenario: example endpoint scenario
    Given There are 5 testModels
    When I send a GET request to path /api/v1/test-models
    Then the response status code should be 200
    And the response should contain the following properties
      | path | value    |
      | data | ~count~5 |
```

The example above will automatically load the feature file with the same name as the test class with `.feature`
extension.  
This is our preferred behavior, but you can go another way by specifying the via the `FeatureFile` attribute on the
class:

```php
// tests/Feature/MyEndpointTest.php
<?php

#[FeatureFile(__DIR__.'/custom-feature.feature')]
class MyEndpointTest extends TestCase
{
    use HasBehatFeature;

    #[DataProvider('featureProvider')]
    public function test_behat_scenario($scenario, $feature)
    {
        $this->executeScenario($scenario, $feature);
    }
}
```

### Qase Test Run Reporter

Integrate with Qase.io for comprehensive test reporting and management.  
Mark a PHPUnit test class with the `\Qase\PHPUnitReporter\Attributes\Suite` attribute, then it will be automatically  
reported to Qase.io when it is executed in `testops` mode.

**PHPUnit Configuration:**

```xml
<!-- phpunit.xml -->
<phpunit>
    <extensions>
        <bootstrap class="Xentral\LaravelTesting\Qase\XentralQaseExtension"/>
    </extensions>
</phpunit>
```

More information can be found in the Qase PHPUnit
Reporter [qase/phpunit-reporter](https://github.com/qase-tms/qase-phpunit/blob/main/composer.json).

### Testing

```bash
composer test
```

## Contributing

### Ideas/Roadmap

Here are some ideas for future development:

* Add support for Behat in Pest.
* Add Support for Qase Test Suite in Pest
* Add support for Outlines in the BehatDumper
* Extend Qase test cases with complete behat scenario information if given

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email manuel@christlieb.eu instead of using the issue tracker.

## Credits

- [Manuel Christlieb](https://github.com/bambamboole)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
