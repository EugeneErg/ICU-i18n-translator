<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class DetectedLanguage
{
    public function __construct(
        public string $language,
        public float $score,
    ) {
    }
}