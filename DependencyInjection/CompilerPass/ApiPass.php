<?php

namespace SensioLabs\Bundle\ConnectBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter('kernel.debug')) {
            $container->getDefinition('sensiolabs_connect.buzz')->addMethodCall('addListener', array(new Reference('sensiolabs_connect.collector.api')));
        }
    }
}
