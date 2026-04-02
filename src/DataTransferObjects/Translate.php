<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\DataTransferObjects;

final readonly class Translate
{
    public function __construct(
        public string $template,
        public array $variables,
    ) {
    }
}