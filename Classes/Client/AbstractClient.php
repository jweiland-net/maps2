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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract client to send Requests to Map Providers
 */
abstract class AbstractClient implements ClientInterface
{
    protected MessageHelper $messageHelper;

    public function __construct(MessageHelper $messageHelper = null)
    {
        $this->messageHelper = $messageHelper ?? GeneralUtility::makeInstance(MessageHelper::class);
    }

    public function processRequest(RequestInterface $request): array
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->getUri(),
                'Invalid request URI',
                AbstractMessage::ERROR
            );
            return [];
        }

        $processedResponse = [];
        $clientReport = [];
        $response = GeneralUtility::getUrl($request->getUri());
        $this->checkClientReportForErrors($clientReport);
        if (!$this->hasErrors()) {
            $processedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $this->checkResponseForErrors($processedResponse);
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

    /**
     * This method will only check the report of the client and not the result itself.
     */
    protected function checkClientReportForErrors(array $clientReport): void
    {
        if (!empty($clientReport['message'])) {
            $this->messageHelper->addFlashMessage(
                $clientReport['message'],
                $clientReport['title'],
                $clientReport['severity']
            );
        }
    }

    abstract protected function checkResponseForErrors(?array $processedResponse): void;
}
