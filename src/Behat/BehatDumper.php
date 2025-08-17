<?php
declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\TableNode;

class BehatDumper
{
    public static function dumpFeature(FeatureNode $featureNode): string
    {
        $currentIndentation = 0;

        $lines = [];
        $lines[] = self::print('Feature: '.$featureNode->getTitle(), $currentIndentation);
        $currentIndentation++;
        if ($description = $featureNode->getDescription()) {
            $lines[] = self::print($description, $currentIndentation);
        }
        foreach ($featureNode->getScenarios() as $scenario) {
            $lines[] = self::dumpScenario($scenario, $currentIndentation);
        }

        return implode("\n", $lines)."\n";
    }

    public static function dumpScenario(ScenarioNode|OutlineNode $scenario, int $currentIndentation = 0): string
    {
        $lines[] = '';

        if ($scenario->getTags()) {
            $lines[] = self::print(implode(' ', array_map(fn ($tag) => "@{$tag}", $scenario->getTags())), $currentIndentation);
        }

        $lines[] = self::print("{$scenario->getKeyword()}: {$scenario->getTitle()}", $currentIndentation);

        $currentIndentation++;
        $lastKeyword = null;
        foreach ($scenario->getSteps() as $step) {
            $keyword = $step->getKeyword() === $lastKeyword
                ? 'And'
                : $step->getKeyword();
            $lines[] = self::print("{$keyword} {$step->getText()}", $currentIndentation);

            $lastKeyword = $step->getKeyword();
            $arguments = $step->getArguments();
            if (empty($arguments)) {
                continue;
            }
            $currentIndentation++;
            $argument = $arguments[0];
            if ($argument instanceof TableNode) {
                $lines[] = self::print($argument->getTableAsString(), $currentIndentation);
            }

            if ($argument instanceof PyStringNode) {
                $lines[] = self::print('"""json', $currentIndentation);
                $lines[] = self::print($argument->getRaw(), $currentIndentation);
                $lines[] = self::print('"""', $currentIndentation);
            }
            $currentIndentation--;
        }
        if ($scenario instanceof OutlineNode) {
            // Here is some implementation missing. Should be extended as soon as needed
            $lines[] = self::print('Examples:', $currentIndentation);
            $currentIndentation++;
            foreach ($scenario->getExamples() as $example) {
                $lines[] = self::print($example->getExampleText(), $currentIndentation);
            }
        }

        return implode("\n", $lines)."\n";
    }

    private static function print(string $text, int $currentIndentation): string
    {
        $indentation = str_repeat('  ', $currentIndentation);
        $text = str_replace("\n", "\n{$indentation}", $text);

        return $indentation.$text;
    }
}
