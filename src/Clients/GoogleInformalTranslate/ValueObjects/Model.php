<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class Model
{
    public function __construct(
        public string $hash,
        public string $fileName,
    ) {
    }
}