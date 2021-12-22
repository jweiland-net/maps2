<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client;

use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Google Maps Client class
 */
class GoogleMapsClientTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var GeocodeRequest
     */
    protected $geocodeRequestProphecy;

    /**
     * @var MessageHelper
     */
    protected $messageHelperProphecy;

    /**
     * @var GoogleMapsClient
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);

        $this->subject = new GoogleMapsClient(
            $this->messageHelperProphecy->reveal()
        );
    }

    protected function tearDown(): void
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
    public function processRequestWithInvalidRequestAddsFlashMessage(): void
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
            ->addFlashMessage(
                'URI is empty or contains invalid chars. URI: https://www.jweiland.net',
                'Invalid request URI',
                2
            )
            ->shouldBeCalled();

        self::assertSame(
            [],
            $this->subject->processRequest($this->geocodeRequestProphecy->reveal())
        );
    }
}
