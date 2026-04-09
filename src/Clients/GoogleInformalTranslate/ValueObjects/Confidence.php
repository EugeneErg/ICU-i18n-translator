<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class Confidence
{
    /**
     * @param string[] $languages
     * @param float[] $values
     * @param string[] $languages2
     */
    public function __construct(
        public array $languages,
        public mixed $unidentifiedField7,
        public array $values,
        public array $languages2,
    ) {
    }
}