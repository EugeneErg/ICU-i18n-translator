<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

final readonly class Translation
{
    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/TranslateTextResponse#Translation
     */
    public function __construct(
        public string $translatedText,
        public ?string $model = null,
        public ?string $detectedLanguageCode = null,
        public ?TranslateTextGlossaryConfig $glossaryConfig = null,
    ) {
    }
}