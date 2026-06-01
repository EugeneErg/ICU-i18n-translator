<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator;

use EugeneErg\IcuI18nTranslator\DataTransferObjects\FilePathContainer;

interface FormatterInterface
{
    public function format(FilePathContainer $file): string;

    public function parse(string $content): FilePathContainer;
}
