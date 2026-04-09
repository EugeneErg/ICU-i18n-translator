<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

final readonly class TransliterationConfig
{
    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/TransliterationConfig
     */
    public function __construct(
        public bool $enableTransliteration = false,
    ) {
    }
}