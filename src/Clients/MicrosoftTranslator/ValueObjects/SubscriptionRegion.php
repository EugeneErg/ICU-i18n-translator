<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\ValueObjects;

final readonly class SubscriptionRegion implements AuthorizationInterface
{
    public function __construct(public string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getHeaderName(): string
    {
        return 'Ocp-Apim-Subscription-Region';
    }
}