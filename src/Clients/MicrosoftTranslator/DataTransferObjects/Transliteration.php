<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Transliteration
{
    public function __construct(
        public string $script,
        public string $text,
        public ?Alignment $alignment = null,
        public ?SentLen $sentLen = null,
    ) {
    }
}