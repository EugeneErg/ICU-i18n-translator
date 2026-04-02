<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\ValueObjects;

final readonly class TranslateId
{
    public function __construct(public string $value)
    {
    }
}