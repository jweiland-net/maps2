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
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Client Factory class
 */
class ClientFactoryTest extends FunctionalTestCase
{
    protected ClientFactory $subject;

    protected ExtConf $extConf;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function createCreatesGoogleMapsClient(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'gm',
        ];

        $this->extConf = new ExtConf(...$config);

        $this->subject = new ClientFactory(
            new MapHelper(
                $this->extConf,
            ),
        );

        self::assertInstanceOf(
            GoogleMapsClient::class,
            $this->subject->create(),
        );
    }

    /**
     * @test
     */
    public function createCreatesOpenStreetMapClient(): void
    {
        $config = [
            'mapProvider' => 'both',
            'defaultMapProvider' => 'osm',
        ];

        $this->extConf = new ExtConf(...$config);

        $this->subject = new ClientFactory(
            new MapHelper(
                $this->extConf,
            ),
        );

        self::assertInstanceOf(
            OpenStreetMapClient::class,
            $this->subject->create(),
        );
    }
}
