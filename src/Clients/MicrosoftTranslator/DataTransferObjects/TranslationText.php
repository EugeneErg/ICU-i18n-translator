<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class TranslationText
{
    public function __construct(public string $text)
    {
    }
}