<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Helper;

use JWeiland\Maps2\Helper\MessageHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test MessageHelper
 */
class MessageHelperTest extends FunctionalTestCase
{
    protected MessageHelper $subject;

    protected array $coreExtensionsToLoad = [
        'extensionmanager',
        'reactions',
    ];

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/maps2',
        'jweiland/events2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);

        $this->subject = new MessageHelper(
            new FlashMessageService(),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function addFlashMessageWithMessageCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            '',
            ContextualFeedbackSeverity::OK,
            false,
        );

        $this->subject->addFlashMessage('Hello');

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages(),
        );
    }

    #[Test]
    public function addFlashMessageWithMessageAndSubjectCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            'Subject',
            ContextualFeedbackSeverity::OK,
            false,
        );

        $this->subject->addFlashMessage('Hello', 'Subject');

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages(),
        );
    }

    #[Test]
    public function addFlashMessageWithAllArgumentsCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            'Subject',
            ContextualFeedbackSeverity::ERROR,
            false,
        );

        $this->subject->addFlashMessage('Hello', 'Subject', ContextualFeedbackSeverity::ERROR);

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages(),
        );
    }

    #[Test]
    public function getAllFlashMessagesReturnsAllFlashMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        // Test two times, to be safe that messages were NOT flushed
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages(false),
        );
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages(false),
        );
    }

    #[Test]
    public function getAllFlashMessagesReturnsAllFlashMessagesAndFlush(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        // Test two times, to be safe that messages were flushed
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages(),
        );
        self::assertCount(
            0,
            $this->subject->getAllFlashMessages(),
        );
    }

    #[Test]
    public function hasMessagesChecksQueueIfThereAreAnyMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        self::assertTrue(
            $this->subject->hasMessages(),
        );
    }

    #[Test]
    public function getFlashMessagesBySeverityAndFlushReturnsFlashMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        // Test two times, to be save that messages were flushed
        self::assertCount(
            1,
            $this->subject->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::ERROR),
        );
        self::assertCount(
            0,
            $this->subject->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::ERROR),
        );

        // Test two times, to be save that messages were flushed
        self::assertCount(
            2,
            $this->subject->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::WARNING),
        );
        self::assertCount(
            0,
            $this->subject->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::WARNING),
        );
    }

    #[Test]
    public function hasErrorMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        self::assertTrue(
            $this->subject->hasErrorMessages(),
        );
    }

    #[Test]
    public function hasErrorMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertFalse(
            $this->subject->hasErrorMessages(),
        );
    }

    #[Test]
    public function getErrorMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        self::assertCount(
            2,
            $this->subject->getErrorMessages(),
        );
    }

    #[Test]
    public function hasWarningMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::WARNING);

        self::assertTrue(
            $this->subject->hasWarningMessages(),
        );
    }

    #[Test]
    public function hasWarningMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::ERROR);

        self::assertFalse(
            $this->subject->hasWarningMessages(),
        );
    }

    #[Test]
    public function getWarningMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::WARNING);

        self::assertCount(
            2,
            $this->subject->getWarningMessages(),
        );
    }

    #[Test]
    public function hasOkMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertTrue(
            $this->subject->hasOkMessages(),
        );
    }

    #[Test]
    public function hasOkMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::WARNING);

        self::assertFalse(
            $this->subject->hasOkMessages(),
        );
    }

    #[Test]
    public function getOkMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertCount(
            2,
            $this->subject->getOkMessages(),
        );
    }

    #[Test]
    public function hasInfoMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::INFO);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::INFO);

        self::assertTrue(
            $this->subject->hasInfoMessages(),
        );
    }

    #[Test]
    public function hasInfoMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::WARNING);

        self::assertFalse(
            $this->subject->hasInfoMessages(),
        );
    }

    #[Test]
    public function getInfoMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::INFO);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::INFO);

        self::assertCount(
            2,
            $this->subject->getInfoMessages(),
        );
    }

    #[Test]
    public function hasNoticeMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::NOTICE);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::NOTICE);

        self::assertTrue(
            $this->subject->hasNoticeMessages(),
        );
    }

    #[Test]
    public function hasNoticeMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::ERROR);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::WARNING);

        self::assertFalse(
            $this->subject->hasNoticeMessages(),
        );
    }

    #[Test]
    public function getNoticeMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', ContextualFeedbackSeverity::WARNING);
        $this->subject->addFlashMessage('all', 'all', ContextualFeedbackSeverity::NOTICE);
        $this->subject->addFlashMessage('together', 'together', ContextualFeedbackSeverity::NOTICE);

        self::assertCount(
            2,
            $this->subject->getNoticeMessages(),
        );
    }
}
