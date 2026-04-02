<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Formatters;

use EugeneErg\Translate\DataTransferObjects\FilePathContainer;

interface FormatterInterface
{
    public function format(FilePathContainer $file): string;

    public function parse(string $content): FilePathContainer;
}