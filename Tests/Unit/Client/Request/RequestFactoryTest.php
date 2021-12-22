<?php

declare(strict_types=1);

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
    public function createCreatesGoogleMapsGeocodeRequest(): void
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
    public function createCreatesOpenStreetMapGeocodeRequest(): void
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
    public function createSanitizesFilenameWithExtension(): void
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
    public function createSanitizesFilenameWithLowerCamelCase(): void
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
    public function createWithNonExistingClassThrowsException(): void
    {
        $this->mapServiceProphecy
            ->getMapProvider()
            ->shouldBeCalled()
            ->willReturn('gm');

        $this->expectExceptionMessage(
            'Class "JWeiland\Maps2\Client\Request\GoogleMaps\NonExistingClass" to create a new Request could not be found'
        );

        $this->subject->create('NonExistingClass', new ExtConf([]));
    }
}
