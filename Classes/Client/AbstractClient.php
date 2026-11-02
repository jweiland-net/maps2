<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Helper\MessageHelper;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * An abstract client for the map providers. Use getResult() to get the
 * decoded response together with a provider independent error.
 */
abstract class AbstractClient implements ClientInterface
{
    public function __construct(
        protected MessageHelper $messageHelper,
        protected RequestFactory $requestFactory,
    ) {}

    public function processRequest(RequestInterface $request): array
    {
        return $this->getResult($request)->response;
    }

    /**
     * Same as processRequest(), but returns the provider independent error, too.
     * For backwards compatibility the error will still be added to the
     * flash message queue.
     */
    public function getResult(RequestInterface $request): ClientResult
    {
        if (!$request->isValidRequest()) {
            // Do not add the URI. It contains the API key.
            return $this->createErrorResult(new ClientError(
                ClientErrorEnum::INVALID_REQUEST,
                'URI is empty or contains invalid chars.',
                'Invalid request URI',
            ));
        }

        $response = $this->sendRequest($request);
        if ($response instanceof ClientError) {
            return $this->createErrorResult($response);
        }

        if ($response->getStatusCode() !== 200) {
            return $this->createErrorResult($this->getHttpError($response->getStatusCode()));
        }

        return $this->processResponse($this->decodeResponse($response));
    }

    public function hasErrors(): bool
    {
        return $this->messageHelper->hasErrorMessages();
    }

    /**
     * @return AbstractMessage[]
     */
    public function getErrors(): array
    {
        return $this->messageHelper->getErrorMessages();
    }

    /**
     * @param array<mixed>|null $processedResponse
     */
    protected function processResponse(?array $processedResponse): ClientResult
    {
        $error = $this->getResponseError($processedResponse);
        if ($error instanceof ClientError) {
            return $this->createErrorResult($error);
        }

        // Backwards compatibility for custom clients which add errors to the flash message queue.
        // Only count errors of this request. The queue may contain errors of previous requests.
        $numberOfErrors = count($this->messageHelper->getErrorMessages(false));
        $this->checkResponseForErrors($processedResponse);
        if ($processedResponse === null
            || count($this->messageHelper->getErrorMessages(false)) > $numberOfErrors
        ) {
            return new ClientResult();
        }

        return new ClientResult($processedResponse);
    }

    /**
     * Returns the error, if the decoded response of the map provider contains one.
     * $processedResponse is NULL, if the response does not contain valid JSON.
     *
     * @param array<mixed>|null $processedResponse
     */
    protected function getResponseError(?array $processedResponse): ?ClientError
    {
        return null;
    }

    /**
     * @param array<mixed>|null $processedResponse
     * @deprecated Implement getResponseError() instead. Will be removed with next major version of maps2.
     */
    protected function checkResponseForErrors(?array $processedResponse): void {}

    protected function createErrorResult(ClientError $error): ClientResult
    {
        $this->messageHelper->addFlashMessage($error->message, $error->title, ContextualFeedbackSeverity::ERROR);

        return new ClientResult([], $error);
    }

    /**
     * Guzzle throws an exception for HTTP status codes 4xx and 5xx,
     * so these responses will be extracted from the exception.
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface|ClientError
    {
        try {
            return $this->requestFactory->request($request->getUri());
        } catch (BadResponseException $badResponseException) {
            // Do not use RequestException here. Since Guzzle 8 it does not provide getResponse() anymore.
            return $badResponseException->getResponse();
        } catch (GuzzleException) {
            // Do not add the exception message. It contains the URI including the API key.
            return new ClientError(ClientErrorEnum::SERVICE_ERROR, 'MapProvider could not be reached', 'Client Error');
        }
    }

    protected function getHttpError(int $statusCode): ClientError
    {
        return new ClientError(
            match ($statusCode) {
                400 => ClientErrorEnum::INVALID_REQUEST,
                401, 403 => ClientErrorEnum::REQUEST_DENIED,
                429 => ClientErrorEnum::QUOTA_EXCEEDED,
                default => ClientErrorEnum::SERVICE_ERROR,
            },
            'MapProvider returns a response with a status code different than 200',
            'Client Error',
        );
    }

    /**
     * Returns NULL, if the response does not contain valid JSON.
     *
     * @return array<mixed>|null
     */
    protected function decodeResponse(ResponseInterface $response): ?array
    {
        try {
            $processedResponse = \json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($processedResponse) ? $processedResponse : null;
    }
}
