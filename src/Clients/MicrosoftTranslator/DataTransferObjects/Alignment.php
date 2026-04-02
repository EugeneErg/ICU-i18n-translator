<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Alignment
{
    public function __construct(public string $proj)
    {
    }
}