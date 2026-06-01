<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\DataTransferObjects;

use EugeneErg\ICUMessageFormatParser\DataTransferObjects\Types;

final readonly class FilePathContainer
{
    /**
     * @param array<self|string|Types> $children
     */
    public function __construct(
        public array $children = [],
    ) {
    }
}
