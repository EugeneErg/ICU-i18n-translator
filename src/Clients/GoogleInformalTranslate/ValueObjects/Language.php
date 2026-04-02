<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class Language
{
    public function __construct(
        public string $name,
        public bool $source,
        public bool $target,
    ) {
    }
}