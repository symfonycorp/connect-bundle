<?php

namespace SensioLabs\Bundle\ConnectBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig.loader.filesystem') || !$container->hasDefinition('profiler')) {
            return;
        }

        $ref = new \ReflectionClass('SensioLabs\Connect\Profiler\ConnectDataCollector');

        $definition = $container->getDefinition('twig.loader.filesystem');
        $definition->addMethodCall('addPath', array(dirname($ref->getFileName()).'/Resources/views', 'ConnectSDK'));
    }
}
