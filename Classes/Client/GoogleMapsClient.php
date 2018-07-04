<?php
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Simple Client for sending requests to Google
 */
class GoogleMapsClient
{
    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * inject flashMessageService
     *
     * @param FlashMessageService $flashMessageService
     * @return void
     */
    public function injectFlashMessageService(FlashMessageService $flashMessageService)
    {
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * Process Google Maps Requests
     *
     * @param RequestInterface $request
     * @return array
     * @throws \Exception
     */
    public function processRequest(RequestInterface $request)
    {
        if (!$request->isValidRequest()) {
            $this->addMessage('Invalid request: ' . $request->getUri());
            return [];
        }

        try {
            $response = GeneralUtility::getUrl($request->getUri());
            $result = json_decode($response, true);
            if (
                $this->requestHasErrors($result)
                && $GLOBALS['TYPO3_CONF_VARS']['BE']['debug']
            ) {
                DebugUtility::debug($response, 'Response of Google Maps GeoCode API');
                $result = [];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1530698645);
        }

        return $result;
    }

    /**
     * Check result from Google Maps Server for errors
     *
     * @param array|null $result
     * @return bool
     * @throws \Exception
     */
    protected function requestHasErrors($result)
    {
        $hasErrors = false;

        if ($result === null) {
            $this->addMessage(
                'The response of Google Maps was not a valid JSON response.',
                'Invalid JSON response',
                FlashMessage::ERROR
            );
            $hasErrors = true;
        }

        if ($result['status'] !== 'OK') {
            $this->addMessage(
                LocalizationUtility::translate('error.noPositionsFound.body', 'maps2'),
                LocalizationUtility::translate('error.noPositionsFound.title', 'maps2'),
                FlashMessage::INFO
            );
            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Add a message to FlashMessage queue
     *
     * @param string $message
     * @param string $title
     * @param int $severity
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addMessage($message, $title = '', $severity = FlashMessage::OK)
    {
        /** @var $flashMessage FlashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity
        );
        $defaultFlashMessageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
