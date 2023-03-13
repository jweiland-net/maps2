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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test Request Factory class
 */
class RequestFactoryTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected RequestFactory $subject;

    protected ExtConf $extConf;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new RequestFactory(
            new MapHelper(
                $this->extConf
            )
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function createCreatesGoogleMapsGeocodeRequest(): void
    {
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('GeocodeRequest', GeneralUtility::makeInstance(ExtConf::class))
        );
    }

    /**
     * @test
     */
    public function createCreatesOpenStreetMapGeocodeRequest(): void
    {
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('osm');

        self::assertInstanceOf(
            \JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest::class,
            $this->subject->create('GeocodeRequest', GeneralUtility::makeInstance(ExtConf::class))
        );
    }

    /**
     * @test
     */
    public function createSanitizesFilenameWithExtension(): void
    {
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('GeocodeRequest.php', GeneralUtility::makeInstance(ExtConf::class))
        );
    }

    /**
     * @test
     */
    public function createSanitizesFilenameWithLowerCamelCase(): void
    {
        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('gm');

        self::assertInstanceOf(
            GeocodeRequest::class,
            $this->subject->create('geocodeRequest', GeneralUtility::makeInstance(ExtConf::class))
        );
    }

    /**
     * @test
     */
    public function createWithNonExistingClassThrowsException(): void
    {
        $this->expectExceptionMessage('Class "JWeiland\\Maps2\\Client\\Request\\GoogleMaps\\NonExistingClass" to create a new Request could not be found');

        $this->extConf->setMapProvider('both');
        $this->extConf->setDefaultMapProvider('gm');

        $this->subject->create('NonExistingClass', GeneralUtility::makeInstance(ExtConf::class));
    }
}
