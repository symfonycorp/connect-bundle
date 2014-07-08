<?php

/*
 * This file is part of the SensioLabsConnectBundle package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ConnectFactory.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class ConnectFactory extends AbstractFactory
{
    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string           $id             The unique id of the firewall
     * @param array            $config         The options array for this listener
     * @param string           $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.sensiolabs_connect.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('security.authentication.provider.sensiolabs_connect'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, $id)
        ;

        return $provider;
    }

    /**
     * Subclasses must return the id of the abstract listener template.
     *
     * Listener definitions should inherit from the AbstractAuthenticationListener
     * like this:
     *
     *    <service id="my.listener.id"
     *             class="My\Concrete\Classname"
     *             parent="security.authentication.listener.abstract"
     *             abstract="true" />
     *
     * In the above case, this method would return "my.listener.id".
     *
     * @return string
     */
    protected function getListenerId()
    {
        return 'security.authentication.listener.sensiolabs_connect';
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'sensiolabs_connect';
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPoint = 'security.authentication.entry_point.sensiolabs_connect.'.$id;
        $container
            ->setDefinition($entryPoint, new DefinitionDecorator('security.authentication.entry_point.sensiolabs_connect'))
        ;

        return $entryPoint;
    }
}
