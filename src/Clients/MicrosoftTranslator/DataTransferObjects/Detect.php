<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Detect
{
    public function __construct(
        public string $language,
        public float $score,
        public bool $isTranslationSupported,
        public bool $isTransliterationSupported,
    ) {
    }
}