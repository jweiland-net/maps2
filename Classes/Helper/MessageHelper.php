<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains methods to create FlashMessage.
 * Further we will implement a central position for Logging
 */
class MessageHelper
{
    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    public function __construct(FlashMessageService $flashMessageService = null)
    {
        $this->flashMessageService = $flashMessageService ?? GeneralUtility::makeInstance(FlashMessageService::class);
    }

    public function addFlashMessage(string $message, string $title = '', int $severity = FlashMessage::OK)
    {
        // We activate storeInSession, so that messages can be displayed when click on Save&Close button.
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            !Environment::isCli()
        );

        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getAllFlashMessages($flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessageQueue()->getAllMessagesAndFlush();
        }
        return $this->getFlashMessageQueue()->getAllMessages();
    }

    public function hasMessages()
    {
        return !empty($this->getAllFlashMessages(false));
    }

    /**
     * @param int $severity Must be one of the constants in AbstractMessage class
     * @return FlashMessage[]
     */
    protected function getFlashMessagesBySeverity(int $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessages($severity);
    }

    /**
     * @param int $severity Must be one of the constants in AbstractMessage class
     * @return FlashMessage[]
     */
    public function getFlashMessagesBySeverityAndFlush(int $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessagesAndFlush($severity);
    }

    public function hasErrorMessages()
    {
        return !empty($this->getErrorMessages(false));
    }

    public function getErrorMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(FlashMessage::ERROR);
        }
        return $this->getFlashMessagesBySeverity(FlashMessage::ERROR);
    }

    public function hasWarningMessages()
    {
        return !empty($this->getWarningMessages(false));
    }

    public function getWarningMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(FlashMessage::WARNING);
        }
        return $this->getFlashMessagesBySeverity(FlashMessage::WARNING);
    }

    public function hasOkMessages()
    {
        return !empty($this->getOkMessages(false));
    }

    public function getOkMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(FlashMessage::OK);
        }
        return $this->getFlashMessagesBySeverity(FlashMessage::OK);
    }

    public function hasInfoMessages()
    {
        return !empty($this->getInfoMessages(false));
    }

    public function getInfoMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(FlashMessage::INFO);
        }
        return $this->getFlashMessagesBySeverity(FlashMessage::INFO);
    }

    public function hasNoticeMessages()
    {
        return !empty($this->getNoticeMessages(false));
    }

    public function getNoticeMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(FlashMessage::NOTICE);
        }
        return $this->getFlashMessagesBySeverity(FlashMessage::NOTICE);
    }

    protected function getFlashMessageQueue(): FlashMessageQueue
    {
        return $this->flashMessageService->getMessageQueueByIdentifier();
    }
}
