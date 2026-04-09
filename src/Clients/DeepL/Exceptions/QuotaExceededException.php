<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\Exceptions;

use Exception;

class QuotaExceededException extends Exception implements DeepLClientExceptionInterface
{
}