<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Client;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Helper\MessageHelper;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract client to send Requests to Map Providers
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    public function __construct(MessageHelper $messageHelper = null)
    {
        $this->messageHelper = $messageHelper ?? GeneralUtility::makeInstance(MessageHelper::class);;
    }

    public function processRequest(RequestInterface $request): array
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->getUri(),
                'Invalid request URI',
                FlashMessage::ERROR
            );
            return [];
        }

        $processedResponse = [];
        $clientReport = [];
        $response = GeneralUtility::getUrl($request->getUri(), 0, null, $clientReport);
        $this->checkClientReportForErrors($clientReport);
        if (!$this->hasErrors()) {
            $processedResponse = json_decode($response, true);
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
     * @return FlashMessage[]
     */
    public function getErrors(): array
    {
        return $this->messageHelper->getErrorMessages();
    }

    /**
     * This method will only check the report of the client and not the result itself.
     *
     * @param array $clientReport
     */
    protected function checkClientReportForErrors(array $clientReport)
    {
        if (!empty($clientReport['message'])) {
            $this->messageHelper->addFlashMessage(
                $clientReport['message'],
                $clientReport['title'],
                $clientReport['severity']
            );
        }
    }

    abstract protected function checkResponseForErrors(?array $processedResponse);
}
