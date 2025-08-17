<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Xentral\LaravelTesting\Behat\BehatMatcherFinder;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

class ListBehatMatchersCommand extends Command
{
    protected $signature = 'xentral:list-behat-matchers {filter?} {--folder= : Filter in the matcher patterns}';

    protected $description = 'List all Behat matchers and their examples';

    public function handle(): int
    {
        $folder = $this->option('folder') ?? config('testing.behat.matcher_lookup_path');
        $this->info('Behat Matchers and Examples');
        $this->line('');

        $matchers = collect([
            ...BehatMatcherFinder::find($folder),
            // Automatically include the traits from the package
            ...BehatMatcherFinder::find(dirname(__DIR__, 2).'/Behat/Traits'),
        ])->unique(fn (BehatMatcher $matcher) => $matcher->definition->getPattern());

        if ($filter = $this->argument('filter')) {
            $matchers = $matchers->filter(fn (BehatMatcher $matcher) => Str::contains($matcher->definition->getPattern(), $filter));
        }

        if ($matchers->isEmpty()) {
            $this->warn('No Behat matchers found.');

            return self::SUCCESS;
        }

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
