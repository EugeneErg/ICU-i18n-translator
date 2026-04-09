<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class DetectedLanguage
{
    public function __construct(
        public string $language,
        public float $score,
    ) {
    }
}