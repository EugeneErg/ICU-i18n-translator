<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class Translate
{
    /**
     * @param UnidentifiedField7[] $unidentifiedField7
     * @param Model[] $models
     */
    public function __construct(
        public ?string $translatedText,
        public ?string $originalText,
        public mixed $unidentifiedField2,
        public ?string $transliteration,
        public ?int $unidentifiedField4,
        public mixed $unidentifiedField5,
        public mixed $unidentifiedField6,
        public ?array $unidentifiedField7,
        public ?array $models,
    ) {
    }
}