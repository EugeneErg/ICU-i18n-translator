<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Exceptions;

use Exception;
use Throwable;

class FileNotFoundException extends Exception implements TranslatorExceptionInterface
{
    public function __construct(string $message = 'File not found.', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
