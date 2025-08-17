<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Then;
use Behat\Step\When;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\TestCase;
use Xentral\LaravelTesting\OpenApi\ValidatesOpenApiSpec;

trait ProvidesBehatHttpMatchers
{
    use EnrichesBehatStepData;
    use ValidatesOpenApiSpec;

    protected ?TestResponse $currentResponse = null;

    #[When('/^I send (a|an invalid|a non-API) ([^\s]+) request to path (from last response location header|[^\s]+)(?:\s+(?P<mods>(?:(?:with|and)\s+(?:(?:Accept|ContentType|Content-Type)\s+[^\s]+|payload|filters))(?:\s+(?:with|and)\s+(?:(?:Accept|ContentType|Content-Type)\s+[^\s]+|payload|filters))*))?$/i')]
    public function iSendARequest(
        string $invalid,
        string $method,
        string $path,
        ?string $mods = null,
        PyStringNode|TableNode|null $data = null
    ): void {
        if ($path === 'from last response location header') {
            if ($this->currentResponse === null) {
                throw new \RuntimeException('No previous response available to extract the location header from.');
            }
            if (! $this->currentResponse->headers->has('Location')) {
                throw new \RuntimeException('No Location header found in the previous response.');
            }
            $path = $this->currentResponse->headers->get('Location');
        }

        if ($invalid === 'an invalid') {
            $this->withoutRequestValidation();
        } elseif ($invalid === 'a non-API') {
            $this->withoutValidation();
        }

        $headers = [];
        $withPayload = false;
        $withFilters = false;

        if ($mods !== null) {
            $matches = [];
            preg_match_all(
                '/(?:with|and)\s+(?:(?P<header>(?P<ht>Accept|ContentType|Content-Type)\s+(?P<hv>\S+))|(?P<payload>payload)|(?P<filters>filters))/i',
                $mods,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $m) {
                if (! empty($m['header'])) {
                    $headers[$m['ht']] = $m['hv'];
                } elseif (! empty($m['payload'])) {
                    $withPayload = true;
                } elseif (! empty($m['filters'])) {
                    $withFilters = true;
                }
            }
        }

        if ($withPayload && $withFilters) {
            throw new \RuntimeException('You cannot use both "with payload" and "with filters" in the same step (Behat only passes one multiline argument).');
        }

        if ($withPayload) {
            if (! $data instanceof PyStringNode) {
                throw new \RuntimeException('Payload as json is required when "with payload" is specified.');
            }
            /** @var array<mixed> $payload */
            $payload = $this->parsePayload($data);
        }

        if ($withFilters) {
            if (! $data instanceof TableNode) {
                throw new \RuntimeException('Filters as table are required when "with filters" is specified.');
            }
            $filters = [];
            foreach ($this->parseTable($data) as $i => $row) {
                $filters[$i] = [
                    'key' => $row['key'],
                    'op' => $row['operator'] ?? $row['op'] ?? null,
                    'value' => $row['value'],
                ];
            }
            $path .= (str_contains($path, '?') ? '&' : '?').http_build_query(['filter' => $filters]);
        }

        $this->currentResponse = $this->json($method, $path, $payload ?? [], $headers);
    }

    #[Then('/^the response (?:status|code|status code) should be(?: equal to| )(\d+)$/')]
    public function theResponseStatusShouldBe(int $statusCode): void
    {
        $this->currentResponse?->assertStatus($statusCode);
    }

    #[Then('the response should contain the following properties')]
    public function theResponseShouldContain(TableNode $table): void
    {
        foreach ($this->parseTable($table) as $row) {
            if (isset($row['path'], $row['value']) === false) {
                throw new \RuntimeException('This matcher requires path, value.');
            }
            $actual = $this->currentResponse?->json($row['path']);
            $expected = $row['value'] === 'null' ? null : $row['value'];
            if (str_starts_with((string) $expected, '~count~')) {
                $amount = (int) str_replace('~count~', '', $expected);
                $expected = '~count~';
            }
            match ($expected) {
                '~count~' => TestCase::assertCount($amount ?? 1, $actual),
                '~exists~' => TestCase::assertNotNull(
                    $actual,
                    sprintf('Response does not contain path `%s`.', $row['path'])
                ),
                default => TestCase::assertEquals($expected, $actual),
            };
        }
    }

    #[Then('/^the response ([^\s]+) header should match ([^$]+)$/')]
    public function theResponseHeaderShouldMatch(string $header, string $value): void
    {
        TestCase::assertNotNull($this->currentResponse, 'No response available to check headers.');
        TestCase::assertTrue(
            $this->currentResponse->headers->has($header),
            sprintf('Request does not have `%s` header.', $header)
        );
        TestCase::assertSame($value, $this->currentResponse->headers->get($header));
    }
}
