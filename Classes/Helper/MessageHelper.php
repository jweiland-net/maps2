<?php

namespace JWeiland\Maps2\Helper;

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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains methods to create FlashMessage. Further we will implement a central position for Logging
 */
class MessageHelper
{
    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * MessageHelper constructor.
     *
     * @param FlashMessageService|null $flashMessageService
     */
    public function __construct(FlashMessageService $flashMessageService = null)
    {
        if ($flashMessageService === null) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        }
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * Add a message to FlashMessage queue
     *
     * @param string $message
     * @param string $title
     * @param int $severity
     */
    public function addFlashMessage(string $message, string $title = '', int $severity = FlashMessage::OK)
    {
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