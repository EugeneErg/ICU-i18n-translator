<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL\ValueObjects;

enum Formality: string
{
    case Default = 'default';
    case More = 'more';
    case Less = 'less';
    case PreferMore = 'prefer_more';
    case PreferLess = 'prefer_less';
}