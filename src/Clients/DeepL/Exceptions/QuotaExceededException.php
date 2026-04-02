<?php

declare(strict_types=1);

namespace EugeneErg\Translate\Clients\DeepL\Exceptions;

use Exception;

class QuotaExceededException extends Exception implements DeepLClientExceptionInterface
{
}