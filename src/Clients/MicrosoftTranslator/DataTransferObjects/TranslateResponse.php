<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class TranslateResponse
{
    /**
     * @param Translation[] $translations
     */
    public function __construct(
        public array $translations,
        public ?DetectedLanguage $detectedLanguage = null,
        public ?Transliteration $transliteration = null,
        public ?TranslationText $sourceText = null,
    ) {
    }
}