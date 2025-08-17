<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;

class BehatFeatureParser
{
    public static function parse(string $featureString): FeatureNode
    {
        $lexer = new Lexer(static::getBehatDefaultKeywords());
        $parser = new Parser($lexer);
        $feature = $parser->parse($featureString);

        return $feature;
    }

    public static function parseFile(string $filePath): FeatureNode
    {
        return static::parse(file_get_contents($filePath));
    }

    public static function getBehatDefaultKeywords(): ArrayKeywords
    {
        return new ArrayKeywords([
            'en' => [
                'feature' => 'Feature',
                'background' => 'Background',
                'scenario' => 'Scenario',
                'scenario_outline' => 'Scenario Outline|Scenario Template',
                'examples' => 'Examples|Scenarios',
                'given' => 'Given',
                'when' => 'When',
                'then' => 'Then',
                'and' => 'And',
                'but' => 'But',
            ],
        ]);
    }
}
