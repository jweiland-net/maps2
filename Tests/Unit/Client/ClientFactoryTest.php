<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client;

use JWeiland\Maps2\Client\ClientFactory;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test Client Factory class
 */
class ClientFactoryTest extends UnitTestCase
{
    /**
     * @var MapService
     */
    protected $mapServiceProphecy;

    /**
     * @var ClientFactory
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->mapServiceProphecy = $this->prophesize(MapService::class);
        GeneralUtility::addInstance(MapService::class, $this->mapServiceProphecy->reveal());

        $this->subject = new ClientFactory();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function createCreatesGoogleMapsClient()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        self::assertInstanceOf(
            GoogleMapsClient::class,
            $this->subject->create()
        );
    }

    /**
     * @test
     */
    public function createCreatesOpenStreetMapClient()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('osm');

        self::assertInstanceOf(
            OpenStreetMapClient::class,
            $this->subject->create()
        );
    }
}
