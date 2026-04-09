<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects;

final readonly class BearerToken implements AuthorizationInterface
{
    public function __construct(public string $value)
    {
    }

    public function getValue(): string
    {
        return 'Bearer ' . $this->value;
    }

    public function getHeaderName(): string
    {
        return 'Authorization';
    }
}