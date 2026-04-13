<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Exceptions;

use Exception;
use Throwable;

class UnexpectedTranslateDirectionException extends Exception implements TranslatorExceptionInterface
{
    public function __construct(string $message = 'Unexpected translate direction.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}