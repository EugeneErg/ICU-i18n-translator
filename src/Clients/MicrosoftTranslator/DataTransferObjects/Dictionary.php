<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Dictionary
{
    /**
     * @param array<string, LanguageTranslation> $translations
     */
    public function __construct(
        public LanguageTranslation $dictionary,
        public array $translations,
    ) {
    }
}