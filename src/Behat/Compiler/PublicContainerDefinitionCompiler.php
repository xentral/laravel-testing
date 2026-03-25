<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Mark all service definitions as public in order to
 * be able to get the services directly from the container.
 */
class PublicContainerDefinitionCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }
}
