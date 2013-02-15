<?php

/*
 * This file is part of the SensioLabs Connect package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\UserProvider;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;

/**
 * ConnectInMemoryFactory creates services for the memory provider.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class ConnectInMemoryFactory implements UserProviderFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config)
    {
        $users = array();
        foreach ($config['users'] as $username => $roles) {
            $users[str_replace('_', '-', $username)] = $roles;
        }

        $definition = $container->setDefinition($id, new DefinitionDecorator('security.user.provider.sensiolabs_connect_in_memory'));
        $definition->setArguments(array($users));
    }

    public function getKey()
    {
        return 'connect_memory';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->fixXmlConfig('user')
            ->children()
                ->arrayNode('users')
                    ->useAttributeAsKey('username')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }
}
