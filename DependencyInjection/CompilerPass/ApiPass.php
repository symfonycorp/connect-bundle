<?php

namespace SymfonyCorp\Bundle\ConnectBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SymfonyCorp\Connect\Api\Api;

/**
 * @internal since connect-bundle v5.1, to be removed in 6.0
 */
class ApiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('http_client') && !(new \ReflectionClass(Api::class))->hasMethod('getStringResponse')) {
            $container->getDefinition('symfony_connect.api')->replaceArgument(1, new Reference('http_client'));
            $container->getDefinition('symfony_connect.oauth_consumer')->replaceArgument(4, new Reference('http_client'));
        }
    }
}
