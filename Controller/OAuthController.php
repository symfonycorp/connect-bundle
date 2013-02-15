<?php

/*
 * This file is part of the SensioLabsConnectBundle package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Bundle\ConnectBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use SensioLabs\Connect\Security\EntryPoint\ConnectEntryPoint;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class OAuthController
{
    private $entryPoint;

    public function __construct(ConnectEntryPoint $entryPoint)
    {
        $this->entryPoint = $entryPoint;
    }

    public function newSessionAction(Request $request)
    {
        return $this->entryPoint->start($request);
    }
}
