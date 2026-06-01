<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\ValueObjects;

use EugeneErg\IcuI18nTranslator\DataTransferObjects\Variable;

final readonly class Translated
{
    /**
     * @param array<string|Variable> $pattern
     */
    public function __construct(public string $locale, public array $pattern)
    {
    }
}
