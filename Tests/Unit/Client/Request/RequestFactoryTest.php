<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client\Request;

use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test Request Factory class
 */
class RequestFactoryTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var MapService
     */
    protected $mapServiceProphecy;

    /**
     * @var RequestFactory
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->mapServiceProphecy = $this->prophesize(MapService::class);
        GeneralUtility::addInstance(MapService::class, $this->mapServiceProphecy->reveal());

        $this->subject = new RequestFactory();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapServiceProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function createCreatesGoogleMapsGeocodeRequest()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('GeocodeRequest', new ExtConf([]))
        );
    }

    /**
     * @test
     */
    public function createCreatesOpenStreetMapGeocodeRequest()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('osm');

        self::assertInstanceOf(
            \JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest::class,
            $this->subject->create('GeocodeRequest', new ExtConf([]))
        );
    }

    /**
     * @test
     */
    public function createSanitizesFilenameWithExtension()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('GeocodeRequest.php', new ExtConf([]))
        );
    }

    /**
     * @test
     */
    public function createSanitizesFilenameWithLowerCamelCase()
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('geocodeRequest', new ExtConf([]))
        );
    }

    /**
     * @test
     */
    public function createWithNonExistingClassThrowsException()
    {
        $this->expectException(\Exception::class);

        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        $this->subject->create('NonExistingClass', new ExtConf([]));
    }
}
