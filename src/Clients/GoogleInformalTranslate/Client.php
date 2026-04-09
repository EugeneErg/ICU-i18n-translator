<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate;

use EugeneErg\IcuI18nTranslator\Clients\Contracts\PsrClient;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\Exceptions\ClientException;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\Exceptions\NetworkException;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\Exceptions\ResponseJsonException;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\Exceptions\TimeoutException;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\Confidence;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\GoogleTranslateResponse;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\GoogleTranslateType;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\Language;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\Model;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\QualityCheck;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\SupportedLanguagesResponse;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\Translate;
use EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects\UnidentifiedField7;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Throwable;

readonly class Client
{
    public function __construct(
        private PsrClient $psrClient,
        private string $apiUrl,
    ) {
    }

    /**
     * @param GoogleTranslateType[] $types
     */
    public function single(
        string $text,
        string $targetLanguage,
        array $types = [],
        ?string $sourceLanguage = null,
    ): GoogleTranslateResponse {
        $uri = $this->makeUri('translate_a/single', [
            'client' => 'gtx',
            'sl' => $sourceLanguage ?? 'auto',
            'tl' => $targetLanguage,
            'dt' => array_column($types, 'value'),
            'q' => $text,
        ]);
        $result = $this->sendRequest($uri);

        return new GoogleTranslateResponse(
            translates: isset($result[0]) ? array_map(static fn (array $translate) => new Translate(
                translatedText: $translate[0],
                originalText: $translate[1],
                unidentifiedField2: $translate[2],
                transliteration: $translate[3],
                unidentifiedField4: $translate[4] ?? null,
                unidentifiedField5: $translate[5] ?? null,
                unidentifiedField6: $translate[6] ?? null,
                unidentifiedField7: isset($translate[7]) ? array_map(static fn (array $unidentifiedField7) => new UnidentifiedField7(
                    unidentifiedField0: $unidentifiedField7[0] ?? null,
                    unidentifiedField1: $unidentifiedField7[1] ?? null,
                ), $translate[7]) : null,
                models: isset($translate[8]) ? array_map(static fn (array $models) => array_map(static fn (array $model) => new Model(
                    hash: $model[0],
                    fileName: $model[1],
                ), $models), $translate[8]) : null,
            ), $result[0]) : null,
            dictionary: $result[1],
            detectedSourceLanguage: $result[2],
            unidentifiedField3: $result[3],
            unidentifiedField4: $result[4],
            alternativeTranslations: $result[5],
            confidenceValue: $result[6],
            qualityCheck: empty($result[7]) ? null : new QualityCheck(
                html: $result[7][0],
                text: $result[7][1],
                unidentifiedField2: $result[7][2],
            ),
            confidence: isset($result[8]) ? new Confidence(
                languages: $result[8][0],
                unidentifiedField7: $result[8][1],
                values: $result[8][2],
                languages2: $result[8][3],
            ) : null,
        );
    }

    public function getSupportedLanguages(): SupportedLanguagesResponse
    {
        $result = $this->sendRequest($this->makeUri('translate_a/l', ['client' => 'gtx']));
        $languages = [];

        foreach ($result['sl'] ?? [] as $language => $name) {
            $languages[$language] = new Language(
                name: $name,
                source: true,
                target: isset($result['tl'][$language]),
            );
        }

        foreach ($result['tl'] as $language => $name) {
            $languages[$language] ??= new Language(name: $name, source: false, target: true);
        }

        unset($languages['auto']);

        return new SupportedLanguagesResponse(languages: $languages, al: $result['al'] ?? []);
    }

    private function sendRequest(string $uri): array
    {
        try {
            $response = $this->psrClient->sendRequest(method: 'GET', uri: $uri, headers: ['Accept' => 'application/json']);
        } catch (NetworkExceptionInterface $exception) {
            throw new NetworkException($exception->getMessage(), previous: $exception);
        } catch (RequestExceptionInterface $exception) {
            throw new TimeoutException($exception->getMessage(), previous: $exception);
        } catch (ClientExceptionInterface $exception) {
            throw new ClientException($exception->getMessage(), previous: $exception);
        }

        $content = $response->getBody()->getContents();
        $statusCode = $response->getStatusCode();

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            if ($statusCode >= 400) {
                $this->handleErrorResponse($statusCode, $response->getReasonPhrase(), $exception);
            }

            throw new ResponseJsonException('Failed to decode response body', previous: $exception);
        }

        if ($statusCode >= 400) {
            throw $this->handleErrorResponse($statusCode, $content);
        }

        return $decoded;
    }


    private function handleErrorResponse(int $statusCode, string $content, ?Throwable $previous = null): ClientException
    {
        return $statusCode >= 500
            ? new NetworkException($content, previous: $previous)
            : new ClientException($content, previous: $previous);
    }

    private function makeUri(string $path, array $parameters = []): string
    {
        return $this->apiUrl . '/' . $path . ($parameters === [] ? '' : '?' . $this->httpBuildQuery($parameters));
    }

    private function httpBuildQuery(array $parameters, string $separator = '&'): string
    {
        $result = [];

        foreach ($parameters as $key => $value) {
            $key = urlencode($key);

            if (is_string($value)) {
                $result[] = $key . '=' . urlencode($value);
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    $result[] = $key . '=' . (is_string($item) ? urlencode($item) : $item);
                }
            }
        }

        return implode($separator, $result);
    }
}