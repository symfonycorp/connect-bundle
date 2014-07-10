<?php

namespace SensioLabs\Bundle\ConnectBundle\Collector;

use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Message\Request as GuzzleRequest;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Guzzle\Plugin\History\HistoryPlugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ApiCollector extends DataCollector
{
    private $history;
    private $data = array();

    public function __construct(HistoryPlugin $history)
    {
        $this->history = $history;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        foreach ($this->history as $data) {
            /** @var GuzzleRequest $request */
            $request = $data['request'];
            /** @var GuzzleResponse $response */
            $response = $data['response'];

            $this->data[] = array(
                'request' => array(
                    'method' => $request->getMethod(),
                    'url' => sprintf('%s%s', $request->getHost(), $request->getResource()),
                    'headers' => $request->getHeaders()->toArray(),
                    'content' => $request instanceof EntityEnclosingRequestInterface ? (string) $request->getBody() : null,
                ),
                'response' => array(
                    'statusCode' => $response->getStatusCode(),
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders()->toArray(),
                    'content' => $response->getBody(true),
                ),
            );
        }
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
