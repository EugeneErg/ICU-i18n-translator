<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\DataTransferObjects;

final readonly class Translate
{
    /**
     * @param array<string, array<string, string|string[]|null>> $cases
     */
    public function __construct(
        public array $cases,
        public string|null $pattern = null,
    ) {
    }
}
