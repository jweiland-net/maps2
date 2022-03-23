<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client;

use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test Open Street Map class
 */
class OpenStreetMapClientTest extends UnitTestCase
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
     * @var OpenStreetMapClient
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);

        $this->subject = new OpenStreetMapClient(
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
