<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use JWeiland\Maps2\Client\ClientFactory;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Client Factory class
 */
class ClientFactoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    #[Test]
    public function createCreatesGoogleMapsClient(): void
    {
        $subject = $this->get(ClientFactory::class);

        self::assertInstanceOf(
            GoogleMapsClient::class,
            $subject->create(MapProviderEnum::GOOGLE_MAPS),
        );
    }

    #[Test]
    public function createCreatesOpenStreetMapClient(): void
    {
        $subject = $this->get(ClientFactory::class);

        self::assertInstanceOf(
            OpenStreetMapClient::class,
            $subject->create(MapProviderEnum::OPEN_STREET_MAP),
        );
    }
}
