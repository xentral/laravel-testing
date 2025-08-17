<?php declare(strict_types=1);

use Xentral\LaravelTesting\Behat\BehatMatcherFinder;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

test('all example attributes match their declared matchers', function (BehatMatcher $matcher) {
    foreach ($matcher->examples as $example) {
        $pattern = $matcher->definition->getPattern();

        if (isRegexPattern($pattern)) {
            // Test that the step text matches the regex pattern
            expect($example->stepText)
                ->toMatch($pattern)
                ->and(getCaptureGroups($example->stepText, $pattern))
                ->toBe($example->matches, "Capture groups for '{$example->stepText}' should match expected values");
        } else {
            // Test literal string pattern
            expect($example->stepText)
                ->toBe($pattern, "Step text should exactly match pattern for {$matcher->className}::{$matcher->methodName}");
        }
    }
})->with(array_map(fn (BehatMatcher $matcher) => [$matcher], BehatMatcherFinder::find(dirname(__DIR__, 3).'/src')));

test('regex pattern matching works correctly', function () {
    // Test that step text matches pattern
    expect('the response status should be 200')
        ->toMatch('/^the response (?:status|code|status code) should be(?: equal to | )(\d+)$/')
        ->and(getCaptureGroups('the response status should be 200', '/^the response (?:status|code|status code) should be(?: equal to | )(\d+)$/'))
        ->toBe(['200']);

    expect('There are 5 users')
        ->toMatch('/^There are ([^\s]+) ([^\s]+)$/')
        ->and(getCaptureGroups('There are 5 users', '/^There are ([^\s]+) ([^\s]+)$/'))
        ->toBe(['5', 'users']);

    // Test that invalid text doesn't match
    expect('invalid step text')
        ->not->toMatch('/^There are ([^\s]+) ([^\s]+)$/');
});

/**
 * Check if a pattern is a regex pattern (starts and ends with /)
 */
function isRegexPattern(?string $pattern): bool
{
    if ($pattern === null) {
        return false;
    }

    return preg_match('/^\/(.*)\/([gimxs]*)$/', $pattern) === 1;
}

/**
 * Extract capture groups from a regex match, excluding named groups
 */
function getCaptureGroups(string $stepText, string $pattern): array
{
    if (! isRegexPattern($pattern)) {
        return [];
    }

    $matches = [];
    $result = preg_match($pattern, $stepText, $matches);

    if ($result !== 1) {
        return [];
    }

    // Return only indexed capture groups (not named ones)
    $captureGroups = [];
    for ($i = 1; $i < count($matches); $i++) {
        if (isset($matches[$i])) {
            $captureGroups[] = $matches[$i];
        }
    }

    return $captureGroups;
}
