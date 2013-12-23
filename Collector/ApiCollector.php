<?php

namespace SensioLabs\Bundle\ConnectBundle\Collector;

use Buzz\Listener\ListenerInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ApiCollector extends DataCollector implements ListenerInterface
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function preSend(RequestInterface $request)
    {
    }

    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        $this->data[] = array(
            'request' => array(
                'method' => $request->getMethod(),
                'url' => sprintf('%s%s', $request->getHost(), $request->getResource()),
                'headers' => $request->getHeaders(),
                'content' => $request->getContent(),
            ),
            'response' => array(
                'statusCode' => $response->getStatusCode(),
                'reasonPhrase' => $response->getReasonPhrase(),
                'headers' => $response->getHeaders(),
                'content' => $response->getContent(),
            ),
        );
    }

    public function getCalls()
    {
        return $this->data;
    }

    public function getName()
    {
        return 'sensiolabs_connect';
    }
}
