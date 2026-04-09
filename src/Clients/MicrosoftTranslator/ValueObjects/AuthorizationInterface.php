<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects;

interface AuthorizationInterface
{
    public function getValue(): string;
    public function getHeaderName(): string;
}