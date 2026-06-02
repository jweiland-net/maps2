<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client\Request;

use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest as GoogleMapsGeocodeRequest;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest as OpenStreetMapGeocodeRequest;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Request Factory class
 */
class RequestFactoryTest extends FunctionalTestCase
{
    protected RequestFactory $subject;

    protected ExtConf $extConf;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    #[Test]
    public function createWithGoogleMapsMapProviderWillReturnGoogleMapsGeocodeRequest(): void
    {
        $subject = $this->get(RequestFactory::class);

        self::assertInstanceOf(
            GoogleMapsGeocodeRequest::class,
            $subject->create(MapProviderEnum::GOOGLE_MAPS),
        );
    }

    #[Test]
    public function createWithOpenStreetMapMapProviderWillReturnOpenStreetMapGeocodeRequest(): void
    {
        $subject = $this->get(RequestFactory::class);

        self::assertInstanceOf(
            OpenStreetMapGeocodeRequest::class,
            $subject->create(MapProviderEnum::OPEN_STREET_MAP),
        );
    }
}
