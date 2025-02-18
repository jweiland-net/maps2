<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Helper\MessageHelper;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Abstract client to send Requests to Map Providers
 */
abstract class AbstractClient implements ClientInterface
{
    public function __construct(
        protected MessageHelper $messageHelper,
        protected RequestFactory $requestFactory,
    ) {}

    public function processRequest(RequestInterface $request): array
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->getUri(),
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );
            return [];
        }

        $processedResponse = [];
        $response = $this->requestFactory->request($request->getUri());
        if ($response->getStatusCode() === 200) {
            $processedResponse = \json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->checkResponseForErrors($processedResponse);
        } else {
            $this->messageHelper->addFlashMessage(
                'MapProvider returns a response with a status code different than 200',
                'Client Error',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        if ($this->hasErrors()) {
            $processedResponse = [];
        }

        return $processedResponse;
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

    abstract protected function checkResponseForErrors(?array $processedResponse): void;
}
