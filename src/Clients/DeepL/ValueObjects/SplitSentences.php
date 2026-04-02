<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL\ValueObjects;

enum SplitSentences: string
{
    case None = '0';
    case Default = '1';
    case NoNewlines = 'nonewlines';
}