<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\Exceptions;

use Exception;

class TooManyRequestsException extends Exception implements DeepLClientExceptionInterface
{
}