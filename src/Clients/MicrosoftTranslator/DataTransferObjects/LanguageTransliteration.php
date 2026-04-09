<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class LanguageTransliteration
{
    /**
     * @param Script[] $scripts
     */
    public function __construct(
        public string $name,
        public string $nativeName,
        public array $scripts,
    ) {
    }
}