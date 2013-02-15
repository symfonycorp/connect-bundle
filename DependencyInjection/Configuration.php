<?php

/*
 * This file is part of the SensioLabsConnectBundle package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Bundle\ConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sensiolabs_connect');

        $rootNode
            ->children()
                ->scalarNode('app_id')->isRequired()->end()
                ->scalarNode('app_secret')->isRequired()->end()
                ->scalarNode('scope')->isRequired()->end()
                ->scalarNode('oauth_callback_path')->defaultValue('/session/callback')->end()
                ->scalarNode('oauth_endpoint')->defaultValue('https://connect.sensiolabs.com')->end()
                ->scalarNode('api_endpoint')->defaultValue('https://connect.sensiolabs.com/api')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
