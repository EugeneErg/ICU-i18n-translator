<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class GoogleTranslateResponse
{
    /**
     * @param Translate[] $translates
     */
    public function __construct(
        public ?array $translates = null,
        public ?array $dictionary = null,
        public ?string $detectedSourceLanguage = null,
        public mixed $unidentifiedField3 = null,
        public mixed $unidentifiedField4 = null,
        public ?array $alternativeTranslations = null,
        public ?float $confidenceValue = null,
        public ?QualityCheck $qualityCheck = null,
        public ?Confidence $confidence = null,
    ) {
    }
}