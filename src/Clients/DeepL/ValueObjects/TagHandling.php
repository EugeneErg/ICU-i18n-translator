<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL\ValueObjects;

enum TagHandling: string
{
    case Xml = 'xml';
    case Html = 'html';
}