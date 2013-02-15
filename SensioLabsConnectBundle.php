<?php

/*
 * This file is part of the SensioLabsConnectBundle package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Bundle\ConnectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\Factory\ConnectFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\UserProvider\ConnectInMemoryFactory;

/**
 * SensioLabsConnectBundle
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class SensioLabsConnectBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->getExtension('security')->addSecurityListenerFactory(new ConnectFactory());
        $container->getExtension('security')->addUserProviderFactory(new ConnectInMemoryFactory());
    }
}
