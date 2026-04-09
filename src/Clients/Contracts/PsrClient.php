<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\Contracts;

use EugeneErg\IcuI18nTranslator\Clients\Contracts\ClientInterface as BaseClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class PsrClient implements BaseClientInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(string $method, string $uri, ?string $body = null, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $name => $value) {
            if ($value !== null) {
                $request = $request->withHeader($name, $value);
            }
        }

        if ($body !== null) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        return $this->client->sendRequest($request);
    }
}