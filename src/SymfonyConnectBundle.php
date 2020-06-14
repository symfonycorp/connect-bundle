<?php

/*
 * This file is part of the symfony/connect-bundle package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Bundle\ConnectBundle;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyCorp\Bundle\ConnectBundle\DependencyInjection\Security\Factory\AuthenticatorConnectFactory;
use SymfonyCorp\Bundle\ConnectBundle\DependencyInjection\Security\Factory\ConnectFactory;
use SymfonyCorp\Bundle\ConnectBundle\DependencyInjection\Security\UserProvider\ConnectInMemoryFactory;
use SymfonyCorp\Bundle\ConnectBundle\DependencyInjection\SymfonyConnectExtension;

/**
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class SymfonyConnectBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new SymfonyConnectExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container)
    {
        if ($container->hasExtension('security')) {
            $container->getExtension('symfony_connect')->enableSecurity();
            $container->getExtension('security')->addUserProviderFactory(new ConnectInMemoryFactory());

            if (interface_exists(AuthenticatorFactoryInterface::class)) {
                $securityFactory = new AuthenticatorConnectFactory();
            } else {
                $securityFactory = new ConnectFactory();
            }
            $container->getExtension('security')->addSecurityListenerFactory($securityFactory);
        }
    }
}
