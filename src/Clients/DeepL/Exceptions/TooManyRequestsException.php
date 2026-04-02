<?php

declare(strict_types=1);

namespace EugeneErg\Translate\Clients\DeepL\Exceptions;

use Exception;

class TooManyRequestsException extends Exception implements DeepLClientExceptionInterface
{
}