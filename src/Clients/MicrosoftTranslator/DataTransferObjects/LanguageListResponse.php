<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class LanguageListResponse
{
    /**
     * @param array<string, LanguageTranslation> $translation
     * @param LanguageTransliteration[] $transliteration
     * @param array<string, Dictionary> $dictionary
     */
    public function __construct(
        public ?array $translation = null,
        public ?array $transliteration = null,
        public ?array $dictionary = null,
    ) {
    }
}