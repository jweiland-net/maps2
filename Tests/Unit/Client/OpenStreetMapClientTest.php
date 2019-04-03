<?php
namespace JWeiland\Maps2\Tests\Unit\Client;

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

use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test Geocode Request class
 */
class OpenStreetMapClientTest extends UnitTestCase
{
    /**
     * @var GeocodeRequest
     */
    protected $geocodeRequestProphecy;

    /**
     * @var MessageHelper
     */
    protected $messageHelperProphecy;

    /**
     * @var OpenStreetMapClient
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);

        $this->subject = new OpenStreetMapClient(
            $this->messageHelperProphecy->reveal()
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->geocodeRequestProphecy,
            $this->messageHelperProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function processRequestWithInvalidRequestAddsFlashMessage()
    {
        $this->geocodeRequestProphecy
            ->isValidRequest()
            ->shouldBeCalled()
            ->willReturn(false);
        $this->geocodeRequestProphecy
            ->getUri()
            ->shouldBeCalled()
            ->willReturn('https://www.jweiland.net');

        $this->messageHelperProphecy
            ->addFlashMessage('Invalid request: https://www.jweiland.net')
            ->shouldBeCalled();

        $this->assertSame(
            [],
            $this->subject->processRequest($this->geocodeRequestProphecy->reveal())
        );
    }
}
