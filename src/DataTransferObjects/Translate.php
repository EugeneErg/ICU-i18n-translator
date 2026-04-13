<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\DataTransferObjects;

final readonly class Translate
{
    public function __construct(
        public array $cases,
        public ?string $pattern = null,
    ) {
    }
}