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

use SensioLabs\Bundle\ConnectBundle\DependencyInjection\CompilerPass\ApiPass;
use SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\Factory\ConnectFactory;
use SensioLabs\Bundle\ConnectBundle\DependencyInjection\Security\UserProvider\ConnectInMemoryFactory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
        $container->addCompilerPass(new ApiPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
