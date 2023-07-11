<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Configuration;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class ExtConfTest
 */
class ExtConfTest extends FunctionalTestCase
{
    protected ExtConf $subject;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = GeneralUtility::makeInstance(ExtConf::class);
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
    public function getMapProviderInitiallyReturnsBothAsString(): void
    {
        self::assertSame(
            'both',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function setMapProviderSetsMapProvider(): void
    {
        $this->subject->setMapProvider('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function getDefaultMapProviderInitiallyReturnsGoogleMapsAsString(): void
    {
        self::assertSame(
            'gm',
            $this->subject->getDefaultMapProvider()
        );
    }

    /**
     * @test
     */
    public function setDefaultMapProviderSetsDefaultMapProvider(): void
    {
        $this->subject->setDefaultMapProvider('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDefaultMapProvider()
        );
    }

    /**
     * @test
     */
    public function getDefaultCountryInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDefaultCountry()
        );
    }

    /**
     * @test
     */
    public function setDefaultCountrySetsDefaultCountry(): void
    {
        $this->subject->setDefaultCountry('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDefaultCountry()
        );
    }

    /**
     * @test
     */
    public function getDefaultLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getDefaultLatitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLatitudeSetsDefaultLatitude(): void
    {
        $this->subject->setDefaultLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getDefaultLatitude()
        );
    }

    /**
     * @test
     */
    public function getDefaultLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getDefaultLongitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLongitudeSetsDefaultLongitude(): void
    {
        $this->subject->setDefaultLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getDefaultLongitude()
        );
    }

    /**
     * @test
     */
    public function getDefaultRadiusInitiallyReturns250(): void
    {
        self::assertSame(
            250,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusSetsDefaultRadius(): void
    {
        $this->subject->setDefaultRadius(123456);

        self::assertSame(
            123456,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusWithStringResultsInInteger(): void
    {
        $this->subject->setDefaultRadius('123Test');

        self::assertSame(
            123,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusWithBooleanResultsInInteger(): void
    {
        $this->subject->setDefaultRadius(true);

        self::assertSame(
            1,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function getExplicitAllowMapProviderRequestsInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsSetsExplicitAllowGoogleMaps(): void
    {
        $this->subject->setExplicitAllowMapProviderRequests(true);
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithStringReturnsTrue(): void
    {
        $this->subject->setExplicitAllowMapProviderRequests('foo bar');
        self::assertTrue($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithZeroReturnsFalse(): void
    {
        $this->subject->setExplicitAllowMapProviderRequests(0);
        self::assertFalse($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function getExplicitAllowMapProviderRequestsBySessionOnlyInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlySetsExplicitAllowGoogleMapsBySessionOnly(): void
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(true);
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithStringReturnsTrue(): void
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly('foo bar');
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithZeroReturnsFalse(): void
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function getInfoWindowContentTemplatePathInitiallyReturnsDefaultPath(): void
    {
        self::assertSame(
            'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathSetsInfoWindowContentTemplatePath(): void
    {
        $this->subject->setInfoWindowContentTemplatePath('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function getGoogleMapsLibraryInitiallyReturnsEmptyString(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        self::assertSame(
            'https://maps.googleapis.com/maps/api/js?key=myApiKey&libraries=places',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithNoPipeWillNotSetGoogleMapsLibrary(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('foo bar');

        self::assertSame(
            '',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithNoHttpInFrontWillNotSetGoogleMapsLibrary(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('www.domain.de/api=|&mobile=1');

        self::assertSame(
            '',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithPipeAndHttpWillSetGoogleMapsLibrary(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('http://www.domain.de/api=|&mobile=1');

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithPipeAndHttpsWillSetGoogleMapsLibrary(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('https://www.domain.de/api=|&mobile=1');

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithHttpUriAndActivatedHttpsWillSetGoogleMapsLibrary(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('http://www.domain.de/api=|&mobile=1');

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function getGoogleMapsGeocodeUriInitiallyReturnsPreConfiguredUri(): void
    {
        self::assertSame(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            $this->subject->getGoogleMapsGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriSetsGoogleMapsGeocodeUri(): void
    {
        $this->subject->setGoogleMapsGeocodeUri('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getGoogleMapsGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeySetsGoogleMapsJavaScriptApiKey(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getGoogleMapsJavaScriptApiKey()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeySetsGoogleMapsGeocodeApiKey(): void
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getGoogleMapsGeocodeApiKey()
        );
    }

    /**
     * @test
     */
    public function getOpenStreetMapGeocodeUriInitiallyReturnsPreConfiguredUri(): void
    {
        self::assertSame(
            'https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1',
            $this->subject->getOpenStreetMapGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriSetsOpenStreetMapGeocodeUri(): void
    {
        $this->subject->setOpenStreetMapGeocodeUri('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOpenStreetMapGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsRedColor(): void
    {
        self::assertSame(
            '#FF0000',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor(): void
    {
        $this->subject->setStrokeColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturns0point8(): void
    {
        self::assertSame(
            0.8,
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity(): void
    {
        $this->subject->setStrokeOpacity(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function getStrokeWeightInitiallyReturnsTwo(): void
    {
        self::assertSame(
            2,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight(): void
    {
        $this->subject->setStrokeWeight(123456);

        self::assertSame(
            123456,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightWithStringResultsInInteger(): void
    {
        $this->subject->setStrokeWeight('123Test');

        self::assertSame(
            123,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightWithBooleanResultsInInteger(): void
    {
        $this->subject->setStrokeWeight(true);

        self::assertSame(
            1,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function getFillColorInitiallyReturnsRedColor(): void
    {
        self::assertSame(
            '#FF0000',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor(): void
    {
        $this->subject->setFillColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturns0point35(): void
    {
        self::assertSame(
            0.35,
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity(): void
    {
        $this->subject->setFillOpacity(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconWidthInitiallyReturns25(): void
    {
        self::assertSame(
            25,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth(): void
    {
        $this->subject->setMarkerIconWidth(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithStringResultsInInteger(): void
    {
        $this->subject->setMarkerIconWidth('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithBooleanResultsInInteger(): void
    {
        $this->subject->setMarkerIconWidth(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconHeightInitiallyReturns40(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight(): void
    {
        $this->subject->setMarkerIconHeight(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithStringResultsInInteger(): void
    {
        $this->subject->setMarkerIconHeight('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithBooleanResultsInInteger(): void
    {
        $this->subject->setMarkerIconHeight(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosXInitiallyReturns13(): void
    {
        self::assertSame(
            13,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX(): void
    {
        $this->subject->setMarkerIconAnchorPosX(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithStringResultsInInteger(): void
    {
        $this->subject->setMarkerIconAnchorPosX('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger(): void
    {
        $this->subject->setMarkerIconAnchorPosX(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosYInitiallyReturns40(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY(): void
    {
        $this->subject->setMarkerIconAnchorPosY(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithStringResultsInInteger(): void
    {
        $this->subject->setMarkerIconAnchorPosY('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger(): void
    {
        $this->subject->setMarkerIconAnchorPosY(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }
}
