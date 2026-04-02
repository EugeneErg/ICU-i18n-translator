<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\ValueObjects;

use EugeneErg\Translate\DataTransferObjects\Variable;

final readonly class Translated
{
    /**
     * @param string $locale
     * @param array<string|Variable> $pattern
     */
    public function __construct(public string $locale, public array $pattern)
    {
    }
}