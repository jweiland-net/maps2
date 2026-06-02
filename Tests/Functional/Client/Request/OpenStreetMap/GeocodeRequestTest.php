<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client\Request\OpenStreetMap;

use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test OpenStreetMap Geocode Request class
 */
class GeocodeRequestTest extends FunctionalTestCase
{
    protected GeocodeRequest $subject;

    protected ExtConf $extConf;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    #[Test]
    public function canProcessWillReturnTrue(): void
    {
        $subject = new GeocodeRequest(new ExtConf());

        self::assertTrue(
            $subject->canProcess(MapProviderEnum::OPEN_STREET_MAP),
        );
    }

    #[Test]
    public function canProcessWillReturnFalse(): void
    {
        $subject = new GeocodeRequest(new ExtConf());

        self::assertFalse(
            $subject->canProcess(MapProviderEnum::GOOGLE_MAPS),
        );
    }

    #[Test]
    public function getUriWithEmptyAddressWillReturnEmptyString(): void
    {
        $subject = new GeocodeRequest(new ExtConf());

        self::assertSame(
            '',
            $subject->getUri(''),
        );
    }

    #[Test]
    public function getUriWillReturnGeocodeUri(): void
    {
        $subject = new GeocodeRequest(new ExtConf());

        self::assertSame(
            'https://nominatim.openstreetmap.org/search?q=Filderstadt&format=json&addressdetails=1',
            $subject->getUri('Filderstadt'),
        );
    }
}
