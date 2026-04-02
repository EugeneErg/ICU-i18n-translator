<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

final readonly class AuthorizingResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public ?string $scope = null,
    ) {
    }
}