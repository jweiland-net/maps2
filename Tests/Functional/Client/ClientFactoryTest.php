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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Client Factory class
 */
class ClientFactoryTest extends FunctionalTestCase
{
    protected ClientFactory $subject;

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

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new ClientFactory(
            new MapHelper(
                $this->extConf,
            ),
        );
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
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('gm');

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
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('osm');

        self::assertInstanceOf(
            OpenStreetMapClient::class,
            $this->subject->create(),
        );
    }
}
