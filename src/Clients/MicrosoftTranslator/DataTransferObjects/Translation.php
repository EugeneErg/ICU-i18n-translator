<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Translation
{
    public function __construct(
        public string $to,
        public string $text,
        public ?Transliteration $transliteration = null,
    ) {
    }
}