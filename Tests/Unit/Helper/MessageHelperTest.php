<?php
namespace JWeiland\Maps2\Tests\Unit\Helper;

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

use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Tests\Unit\AbstractUnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test MessageHelper
 */
class MessageHelperTest extends AbstractUnitTestCase
{
    /**
     * @var MessageHelper
     */
    protected $subject;

    /**
     * @var FlashMessageService|ObjectProphecy
     */
    protected $flashMessageServiceProphecy;

    /**
     * @var FlashMessageQueue|ObjectProphecy
     */
    protected $flashMessageQueueProphecy;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->flashMessageQueueProphecy = $this->prophesize(FlashMessageQueue::class);
        $this->flashMessageServiceProphecy = $this->prophesize(FlashMessageService::class);
        $this->flashMessageServiceProphecy
            ->getMessageQueueByIdentifier()
            ->shouldBeCalled()
            ->willReturn($this->flashMessageQueueProphecy->reveal());

        $this->subject = new MessageHelper($this->flashMessageServiceProphecy->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->flashMessageServiceProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function addFlashMessageWithMessageCallsEnqueue()
    {
        $expectedFlashMessage = new FlashMessage('Hallo');
        GeneralUtility::addInstance(FlashMessage::class, $expectedFlashMessage);
        $this->flashMessageQueueProphecy->enqueue($expectedFlashMessage)->shouldBeCalled();

        $this->subject->addFlashMessage(
            'Hello'
        );
    }

    /**
     * @test
     */
    public function addFlashMessageWithMessageAndSubjectCallsEnqueue()
    {
        $expectedFlashMessage = new FlashMessage('Hallo', 'Subject');
        GeneralUtility::addInstance(FlashMessage::class, $expectedFlashMessage);
        $this->flashMessageQueueProphecy->enqueue($expectedFlashMessage)->shouldBeCalled();

        $this->subject->addFlashMessage(
            'Hello',
            'Subject'
        );
    }

    /**
     * @test
     */
    public function addFlashMessageWithAllArgumentsCallsEnqueue()
    {
        $expectedFlashMessage = new FlashMessage(
            'Hallo',
            'Subject',
            FlashMessage::ERROR
        );
        GeneralUtility::addInstance(FlashMessage::class, $expectedFlashMessage);
        $this->flashMessageQueueProphecy->enqueue($expectedFlashMessage)->shouldBeCalled();

        $this->subject->addFlashMessage(
            'Hello',
            'Subject',
            FlashMessage::ERROR
        );
    }
}
