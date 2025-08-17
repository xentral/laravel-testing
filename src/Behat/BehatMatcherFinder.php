<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat;

use Behat\Step\Definition;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Xentral\LaravelTesting\Behat\Attributes\Example;
use Xentral\LaravelTesting\Behat\Dto\BehatMatcher;

class BehatMatcherFinder
{
    public static function find(string $folder): array
    {
        $behatMatches = [];

        foreach (self::findPhpFqcnsRecursively($folder) as $class) {
            try {
                $reflection = new \ReflectionClass($class);

                foreach ($reflection->getMethods() as $method) {
                    /** @var \ReflectionAttribute[] $stepAttributes */
                    $stepAttributes = [
                        ...$method->getAttributes(Given::class),
                        ...$method->getAttributes(When::class),
                        ...$method->getAttributes(Then::class),
                    ];
                    if (empty($stepAttributes)) {
                        continue;
                    }
                    if (count($stepAttributes) > 1) {
                        throw new \Exception("Method {$method->getName()} has more than one step attribute");
                    }
                    /** @var Definition $stepInstance */
                    $matcher = $stepAttributes[0]->newInstance();
                    $examples = [];
                    foreach ($method->getAttributes(Example::class) as $exampleAttribute) {
                        /** @var Example $exampleInstance */
                        $examples[] = $exampleAttribute->newInstance();
                    }
                    $behatMatches[] = new BehatMatcher($stepAttributes[0]->getName(), $matcher, $examples, $class, $method->getName(), $reflection->getFileName());
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $behatMatches;
    }

    /**
     * @return class-string
     */
    private static function findPhpFqcnsRecursively(string $directory): array
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        if (! is_dir($directory)) {
            throw new \InvalidArgumentException("Path is not a directory: {$directory}");
        }

        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $fqcns = [];

        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        /** @var \SplFileInfo $file */
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $code = @file_get_contents($file->getPathname());
            if ($code === false) {
                continue;
            }

            $ast = $parser->parse($code);
            if ($ast === null) {
                continue;
            }

            $traverser = new NodeTraverser;
            $traverser->addVisitor(new NameResolver);

            $collected = [];
            $traverser->addVisitor(new class($collected) extends NodeVisitorAbstract
            {
                /** @var list<string> */
                public array $out = [];

                public function __construct(public array &$collected) {}

                public function enterNode(Node $node)
                {
                    if ($node instanceof Class_ || $node instanceof Trait_) {
                        // NameResolver populates namespacedName for ClassLike nodes.
                        if (isset($node->namespacedName)) {
                            $this->out[] = $node->namespacedName->toString();
                        }
                    }

                    return null;
                }

                public function afterTraverse(array $nodes)
                {
                    $this->collected = array_merge($this->collected, $this->out);
                }
            });

            $traverser->traverse($ast);

            // Pull results out of the anonymous visitor (stored by reference)
            $refObj = new \ReflectionObject($traverser);
            foreach ($refObj->getProperties() as $prop) {
                if ($prop->getName() === 'visitors') {
                    $visitors = $prop->getValue($traverser);
                    foreach ($visitors as $v) {
                        if (property_exists($v, 'collected')) {
                            /** @var array $v ->collected */
                            $fqcns = array_merge($fqcns, $v->collected);
                        }
                    }
                    break;
                }
            }
        }

        // Unique, sorted, reindex
        $fqcns = array_values(array_unique($fqcns));
        sort($fqcns);

        return $fqcns;
    }
}
