<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\DataTransferObjects;

final readonly class Language
{
    public function __construct(
        public string $language,
        public string $name,
        public bool $supportsFormality
    ) {
    }
}