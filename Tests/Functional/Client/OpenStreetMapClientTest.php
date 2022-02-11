<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Test Open Street Map class
 */
class OpenStreetMapClientTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected OpenStreetMapClient $subject;

    protected ExtConf $extConf;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = new ExtConf();
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);

        $this->subject = new OpenStreetMapClient(
            $this->messageHelperProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->messageHelperProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function processRequestWithEmptyUriAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('');

        $this->messageHelperProphecy
            ->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ',
                'Invalid request URI',
                2
            )
            ->shouldBeCalled();

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest)
        );
    }

    /**
     * @test
     */
    public function processRequestWithInvalidRequestAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('https://www.jweiländ.net');

        $this->messageHelperProphecy
            ->addFlashMessage(
                'URI is empty or contains invalid chars. URI: https://www.jweiländ.net',
                'Invalid request URI',
                2
            )
            ->shouldBeCalled();

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest)
        );
    }
}