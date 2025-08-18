<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Xentral\LaravelTesting\Behat\BehatMatcherFinder;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

use function Laravel\Prompts\search;

class ListBehatMatchersCommand extends Command
{
    protected $signature = 'xentral:list-behat-matchers {--folder= : Folder to search for matchers} {--non-interactive : Skip interactive search}';

    protected $description = 'Interactively search and list Behat matchers and their examples';

    public function handle(): int
    {
        $this->info('Behat Matchers and Examples');
        $this->line('');

        $folders = $this->option('folder')
            ? [$this->option('folder')]
            : [config('testing.behat.matcher_lookup_path'), dirname(__DIR__, 2).'/Behat/Traits'];

        $cacheKey = 'behat_matchers_'.md5(implode(',', $folders));
        $matchers = Cache::remember($cacheKey, 60, fn () => collect(BehatMatcherFinder::find(...$folders)));

        if ($matchers->isEmpty()) {
            $this->warn('No Behat matchers found.');

            return self::SUCCESS;
        }

        if ($this->option('non-interactive')) {
            return $this->displayAllMatchers($matchers);
        }

        return $this->runInteractiveSearch($matchers);
    }

    private function runInteractiveSearch(Collection $matchers): int
    {
        $matcherOptions = $matchers->mapWithKeys(function (BehatMatcher $matcher) {
            $keyword = Str::afterLast($matcher->keyword, '\\');
            $className = basename($matcher->className);
            $label = "{$keyword}: {$matcher->definition->getPattern()} ({$className}::{$matcher->methodName})";

            return [$matcher->definition->getPattern() => $label];
        })->all();

        $selectedPattern = search(
            label: 'Search for a Behat matcher:',
            options: fn (string $value) => array_filter(
                $matcherOptions,
                fn ($label, $pattern) => empty($value) ||
                    Str::contains($pattern, $value, true) ||
                    Str::contains($label, $value, true),
                ARRAY_FILTER_USE_BOTH
            ),
            placeholder: 'Start typing to search...',
        );

        if ($selectedPattern === '') {
            $this->info('No matcher selected.');

            return self::SUCCESS;
        }

        $selectedMatcher = $matchers->first(
            fn (BehatMatcher $matcher) => $matcher->definition->getPattern() === $selectedPattern
        );

        if ($selectedMatcher) {
            $this->displayMatcher($selectedMatcher);
        }

        return self::SUCCESS;
    }

    private function displayAllMatchers(Collection $matchers): int
    {
        foreach ($matchers as $matcher) {
            $this->displayMatcher($matcher);
            $this->line('');
        }

        $matcherCount = $matchers->count();
        $exampleCount = $matchers->map(fn (BehatMatcher $matcher) => count($matcher->examples))->sum();

        $this->info("Found {$matcherCount} matchers with {$exampleCount} total examples.");

        return self::SUCCESS;
    }

    private function displayMatcher(BehatMatcher $matcher): void
    {
        $className = basename($matcher->className);
        $this->line("<fg=cyan>Class:</> {$className}::{$matcher->methodName}");
        $keyword = Str::afterLast($matcher->keyword, '\\');
        $this->line("<fg=green>{$keyword}:</> {$matcher->definition->getPattern()}");
        if (empty($matcher->examples)) {
            $this->line('<fg=red>No examples found</>');

            return;
        }

        $data = array_map(
            fn ($example) => [$example->stepText, implode(', ', array_map(fn ($match) => is_string($match) ? "'{$match}'" : json_encode($match), $example->matches))],
            $matcher->examples,
        );
        $this->table(['Text', 'Captures'], $data);
    }
}
