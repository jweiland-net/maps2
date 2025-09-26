<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client\Request;

use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Request Factory class
 */
class RequestFactoryTest extends FunctionalTestCase
{
    protected RequestFactory $subject;

    protected ExtConf $extConf;

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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function createCreatesGoogleMapsGeocodeRequest(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'gm',
        ];
        $extConf = new ExtConf(...$config);

        $subject = new RequestFactory(
            new MapHelper(
                $extConf,
            ),
        );

        self::assertInstanceOf(
            GeocodeRequest::class,
            $subject->create('GeocodeRequest'),
        );
    }

    #[Test]
    public function createCreatesOpenStreetMapGeocodeRequest(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'osm',
        ];
        $extConf = new ExtConf(...$config);

        $subject = new RequestFactory(
            new MapHelper(
                $extConf,
            ),
        );

        self::assertInstanceOf(
            \JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest::class,
            $subject->create('GeocodeRequest'),
        );
    }

    #[Test]
    public function createSanitizesFilenameWithExtension(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'gm',
        ];
        $extConf = new ExtConf(...$config);

        $subject = new RequestFactory(
            new MapHelper(
                $extConf,
            ),
        );

        self::assertInstanceOf(
            GeocodeRequest::class,
            $subject->create('GeocodeRequest.php'),
        );
    }

    #[Test]
    public function createSanitizesFilenameWithLowerCamelCase(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'gm',
        ];
        $extConf = new ExtConf(...$config);

        $subject = new RequestFactory(
            new MapHelper(
                $extConf,
            ),
        );

        self::assertInstanceOf(
            GeocodeRequest::class,
            $subject->create('geocodeRequest'),
        );
    }

    #[Test]
    public function createWithNonExistingClassThrowsException(): void
    {
        $this->expectExceptionMessage('Class "JWeiland\\Maps2\\Client\\Request\\GoogleMaps\\NonExistingClass" to create a new Request could not be found');

        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'gm',
        ];
        $extConf = new ExtConf(...$config);

        $subject = new RequestFactory(
            new MapHelper(
                $extConf,
            ),
        );

        $subject->create('NonExistingClass');
    }
}
