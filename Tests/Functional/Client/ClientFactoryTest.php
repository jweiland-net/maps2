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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test Client Factory class
 */
class ClientFactoryTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected ClientFactory $subject;

    protected ExtConf $extConf;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new ClientFactory(
            new MapHelper(
                $this->extConf
            )
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapServiceProphecy,
            $this->extConf
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
            $this->subject->create()
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
            $this->subject->create()
        );
    }
}
