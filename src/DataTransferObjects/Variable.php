<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\DataTransferObjects;

final readonly class Variable
{
    public function __construct(public int $value)
    {
    }
}