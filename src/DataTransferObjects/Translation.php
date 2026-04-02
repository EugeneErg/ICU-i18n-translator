<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\DataTransferObjects;

final readonly class Translation
{
    public function __construct(public string $language, public string $value)
    {
    }
}