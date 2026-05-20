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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains methods to create FlashMessages.
 * Further we will implement a central position for Logging
 */
class MessageHelper
{
    public function __construct(protected FlashMessageService $flashMessageService)
    {
    }

    public function addFlashMessage(string $message, string $title = '', ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK): void
    {
        // We activate storeInSession, so that messages can be displayed when click on Save&Close button.
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            !Environment::isCli(),
        );

        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    /**
     * @return FlashMessage[]
     */
    public function getAllFlashMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessageQueue()->getAllMessagesAndFlush();
        }

        return $this->getFlashMessageQueue()->getAllMessages();
    }

    public function hasMessages(): bool
    {
        return $this->getAllFlashMessages(false) !== [];
    }

    /**
     * @param ContextualFeedbackSeverity $severity Must be one of the enum values in ContextualFeedbackSeverity class
     * @return FlashMessage[]
     */
    protected function getFlashMessagesBySeverity(ContextualFeedbackSeverity $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessages($severity);
    }

    /**
     * @param ContextualFeedbackSeverity $severity Must be one of the enum values in ContextualFeedbackSeverity class
     * @return FlashMessage[]
     */
    public function getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessagesAndFlush($severity);
    }

    public function hasErrorMessages(): bool
    {
        return $this->getErrorMessages(false) !== [];
    }

    /**
     * @return AbstractMessage[]
     */
    public function getErrorMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::ERROR);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::ERROR);
    }

    public function hasWarningMessages(): bool
    {
        return $this->getWarningMessages(false) !== [];
    }

    /**
     * @return AbstractMessage[]
     */
    public function getWarningMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::WARNING);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::WARNING);
    }

    public function hasOkMessages(): bool
    {
        return $this->getOkMessages(false) !== [];
    }

    /**
     * @return AbstractMessage[]
     */
    public function getOkMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::OK);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::OK);
    }

    public function hasInfoMessages(): bool
    {
        return $this->getInfoMessages(false) !== [];
    }

    /**
     * @return AbstractMessage[]
     */
    public function getInfoMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::INFO);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::INFO);
    }

    public function hasNoticeMessages(): bool
    {
        return $this->getNoticeMessages(false) !== [];
    }

    /**
     * @return AbstractMessage[]
     */
    public function getNoticeMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::NOTICE);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::NOTICE);
    }

    protected function getFlashMessageQueue(): FlashMessageQueue
    {
        return $this->flashMessageService->getMessageQueueByIdentifier('extbase.flashmessages.maps2');
    }
}
