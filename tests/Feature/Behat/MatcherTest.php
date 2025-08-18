<?php declare(strict_types=1);

use Xentral\LaravelTesting\Behat\BehatMatcherChecker;
use Xentral\LaravelTesting\Behat\BehatMatcherFinder;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

test('all example attributes match their declared matchers', function (BehatMatcher $matcher) {
    BehatMatcherChecker::check($matcher);
})->with(
    fn () => array_map(
        fn (BehatMatcher $matcher) => [$matcher],
        BehatMatcherFinder::find(dirname(__DIR__, 3).'/src'),
    )
);
