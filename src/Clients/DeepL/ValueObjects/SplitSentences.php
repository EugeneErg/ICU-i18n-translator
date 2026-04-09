<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\ValueObjects;

enum SplitSentences: string
{
    case None = '0';
    case Default = '1';
    case NoNewlines = 'nonewlines';
}