<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleOauth;

use EugeneErg\Translate\Clients\Contracts\PsrClient;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthAccessDeniedException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthAdminPolicyEnforcedException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthClientException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthDisabledClientException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthHttpException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthInvalidClientException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthInvalidGrantException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthInvalidScopeException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthNetworkException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthOrgInternalException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthResponseJsonException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthTimeoutException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthUnauthorizedClientException;
use EugeneErg\Translate\Clients\GoogleOauth\Exceptions\GoogleOAuthUnknownApiException;
use EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects\AuthorizingResponse;
use Firebase\JWT\JWT;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

readonly class Client
{
    public function __construct(
        private PsrClient $psrClient,
        private string $apiUrl,
    ) {
    }

    /**
     * @link https://developers.google.com/identity/protocols/oauth2/service-account#authorizingrequests
     */
    public function serviceAccountAuthorizing(array $scopes, string $clientEmail, string $privateKey): AuthorizingResponse
    {
        $uri = $this->makeUri('token');
        $now = time();
        $result = $this->sendRequest(
            'POST',
            $uri,
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => JWT::encode(
                    payload: [
                        'iss' => $clientEmail,
                        'scope' => implode(' ', $scopes),
                        'aud' => $uri,
                        'exp' => $now + 3600,
                        'iat' => $now,
                    ],
                    key: $privateKey,
                    alg: 'RS256',
                ),
            ],
            $this->makeHeaders(),
        );

        return new AuthorizingResponse(
            accessToken: $result['access_token'],
            tokenType: $result['token_type'],
            expiresIn: $result['expires_in'],
            scope: $result['scope'] ?? null,
        );
    }

    private function sendRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        $body = $data === [] ? null : http_build_query($data);

        try {
            $response = $this->psrClient->sendRequest(method: $method, uri: $url, body: $body, headers: $headers);
        } catch (NetworkExceptionInterface $exception) {
            throw new GoogleOAuthNetworkException('Network failure', previous: $exception);
        } catch (RequestExceptionInterface $exception) {
            throw new GoogleOAuthTimeoutException('Request timeout', previous: $exception);
        } catch (ClientExceptionInterface $exception) {
            throw new GoogleOAuthClientException('HTTP request failed', previous: $exception);
        }

        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();

        if ($statusCode >= 400) {
            throw $this->handleErrorResponse($statusCode, $content);
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new GoogleOAuthResponseJsonException('Failed to decode response body', previous: $exception);
        }

        return $decoded;
    }

    private function makeUri(string $path): string
    {
        return $this->apiUrl . '/' . $path;
    }

    private function makeHeaders(): array
    {
        return ['Content-Type' => 'application/x-www-form-urlencoded'];
    }

    private function handleErrorResponse(int $statusCode, string $content): GoogleOAuthException
    {
        try {
            $errorData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new GoogleOAuthHttpException(
                sprintf('HTTP error %d with invalid response', $statusCode),
                $statusCode,
                $exception,
            );
        }

        if (isset($errorbands['error'])) {
            $errorCode = $errorData['error'];
            $errorDescription = $errorData['error_description'] ?? 'No description provided';

            return match ($errorCode) {
                'unauthorized_client' => new GoogleOAuthUnauthorizedClientException($errorDescription, $statusCode),
                'access_denied' => new GoogleOAuthAccessDeniedException($errorDescription, $statusCode),
                'admin_policy_enforced' => new GoogleOAuthAdminPolicyEnforcedException($errorDescription, $statusCode),
                'invalid_client' => new GoogleOAuthInvalidClientException($errorDescription, $statusCode),
                'invalid_grant' => new GoogleOAuthInvalidGrantException($errorDescription, $statusCode),
                'invalid_scope' => new GoogleOAuthInvalidScopeException($errorDescription, $statusCode),
                'disabled_client' => new GoogleOAuthDisabledClientException($errorDescription, $statusCode),
                'org_internal' => new GoogleOAuthOrgInternalException($errorDescription, $statusCode),
                default => new GoogleOAuthUnknownApiException($errorCode . ': ' . $errorDescription, $errorCode),
            };
        }

        return new GoogleOAuthHttpException(sprintf('HTTP error %d', $statusCode), $statusCode);
    }
}