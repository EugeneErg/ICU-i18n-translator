<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions;

use RuntimeException;

class ClientException extends RuntimeException implements MicrosoftTranslatorExceptionInterface
{
}