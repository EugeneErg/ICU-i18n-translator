<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\Contracts;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(string $method, string $uri, ?string $body = null, array $headers = []): ResponseInterface;
}