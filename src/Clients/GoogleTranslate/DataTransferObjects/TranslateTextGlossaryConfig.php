<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

final readonly class TranslateTextGlossaryConfig
{
    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/TranslateTextGlossaryConfig
     */
    public function __construct(
        public string $glossary,
        public bool $ignoreCase = false,
    ) {
    }
}