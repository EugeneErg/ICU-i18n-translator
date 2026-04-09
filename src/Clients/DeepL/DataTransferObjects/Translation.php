<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\DataTransferObjects;

final readonly class Translation
{
    public function __construct(
        public string $detectedSourceLanguage,
        public string $text
    ) {
    }
}