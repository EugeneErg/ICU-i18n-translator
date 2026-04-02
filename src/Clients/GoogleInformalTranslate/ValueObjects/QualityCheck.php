<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class QualityCheck
{
    /**
     * @param int[] $unidentifiedField2
     */
    public function __construct(
        public string $html,
        public string $text,
        public array $unidentifiedField2,
    ) {
    }
}