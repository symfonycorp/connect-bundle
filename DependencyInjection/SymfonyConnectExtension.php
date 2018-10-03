<?php

/*
 * This file is part of the SymfonyCorpConnectBundle package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Bundle\ConnectBundle\DependencyInjection;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * SymfonyCorpConnectExtension.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class SymfonyConnectExtension extends Extension
{
    private $securityEnabled = false;

    public function enableSecurity()
    {
        $this->securityEnabled = true;
    }

    public function getAlias()
    {
        return 'symfony_connect';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('connect.xml');
        if ($this->securityEnabled) {
            $loader->load('security.xml');
        }

        $container->getDefinition('symfony_connect.oauth_consumer')
            ->replaceArgument(0, $config['app_id'])
            ->replaceArgument(1, $config['app_secret'])
            ->replaceArgument(2, $config['scope'])
            ->replaceArgument(3, $config['oauth_endpoint'])
        ;

        $container->getDefinition('symfony_connect.oauth_consumer')
            ->addMethodCall('setStrictChecks', array($config['strict_checks']))
        ;

        $container->getDefinition('symfony_connect.api')
            ->replaceArgument(0, $config['api_endpoint'])
        ;

        $container->setParameter('symfony_connect.oauth.session_callback_path', $config['oauth_callback_path']);
        $container->setParameter('symfony_connect.api.app_id', $config['app_id']);
        $container->setParameter('symfony_connect.api.app_secret', $config['app_secret']);

        $container->getDefinition('symfony_connect.buzz.client')
            ->addMethodCall('setTimeout', array($config['timeout']))
        ;
    }
}
