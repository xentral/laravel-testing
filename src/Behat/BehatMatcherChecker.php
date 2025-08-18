<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat;

use PHPUnit\Framework\Assert;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

class BehatMatcherChecker
{
    public static function check(BehatMatcher $matcher): void
    {
        $pattern = $matcher->definition->getPattern();
        foreach ($matcher->examples as $example) {
            if (self::isRegexPattern($pattern)) {
                Assert::assertMatchesRegularExpression($pattern, $example->stepText);
                Assert::assertSame($example->matches, self::getCaptureGroups($example->stepText, $pattern));
            } else {
                Assert::assertSame($pattern, $example->stepText);
            }
        }
    }

    /**
     * Check if a pattern is a regex pattern (starts and ends with /)
     */
    private static function isRegexPattern(?string $pattern): bool
    {
        if ($pattern === null) {
            return false;
        }

        return preg_match('/^\/(.*)\/([gimxs]*)$/', $pattern) === 1;
    }

    /**
     * Extract capture groups from a regex match, excluding named groups
     */
    private static function getCaptureGroups(string $stepText, string $pattern): array
    {
        if (! self::isRegexPattern($pattern)) {
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
}
