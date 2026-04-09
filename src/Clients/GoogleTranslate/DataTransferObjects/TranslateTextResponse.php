<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

final readonly class TranslateTextResponse
{
    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/TranslateTextResponse
     *
     * @param Translation[] $translations
     * @param Translation[] $glossaryTranslations
     */
    public function __construct(
        public array $translations,
        public array $glossaryTranslations = [],
    ) {
    }
}