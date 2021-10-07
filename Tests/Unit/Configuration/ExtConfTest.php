<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Configuration;

use JWeiland\Maps2\Configuration\ExtConf;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class ExtConfTest
 */
class ExtConfTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new ExtConf([]);
    }

    protected function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getMapProviderInitiallyReturnsBothAsString()
    {
        self::assertSame(
            'both',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function setMapProviderSetsMapProvider()
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
    public function setMapProviderWithIntegerResultsInString()
    {
        $this->subject->setMapProvider(123);
        self::assertSame('123', $this->subject->getMapProvider());
    }

    /**
     * @test
     */
    public function setMapProviderWithBooleanResultsInString()
    {
        $this->subject->setMapProvider(true);
        self::assertSame('1', $this->subject->getMapProvider());
    }

    /**
     * @test
     */
    public function getDefaultMapProviderInitiallyReturnsGoogleMapsAsString()
    {
        self::assertSame(
            'gm',
            $this->subject->getDefaultMapProvider()
        );
    }

    /**
     * @test
     */
    public function setDefaultMapProviderSetsDefaultMapProvider()
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
    public function setDefaultMapProviderWithIntegerResultsInString()
    {
        $this->subject->setDefaultMapProvider(123);
        self::assertSame('123', $this->subject->getDefaultMapProvider());
    }

    /**
     * @test
     */
    public function setDefaultMapProviderWithBooleanResultsInString()
    {
        $this->subject->setDefaultMapProvider(true);
        self::assertSame('1', $this->subject->getDefaultMapProvider());
    }

    /**
     * @test
     */
    public function getDefaultCountryInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getDefaultCountry()
        );
    }

    /**
     * @test
     */
    public function setDefaultCountrySetsDefaultCountry()
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
    public function setDefaultCountryWithIntegerResultsInString()
    {
        $this->subject->setDefaultCountry(123);
        self::assertSame('123', $this->subject->getDefaultCountry());
    }

    /**
     * @test
     */
    public function setDefaultCountryWithBooleanResultsInString()
    {
        $this->subject->setDefaultCountry(true);
        self::assertSame('1', $this->subject->getDefaultCountry());
    }

    /**
     * @test
     */
    public function getDefaultLatitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getDefaultLatitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLatitudeSetsDefaultLatitude()
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
    public function getDefaultLongitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getDefaultLongitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLongitudeSetsDefaultLongitude()
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
    public function getDefaultRadiusInitiallyReturns250()
    {
        self::assertSame(
            250,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusSetsDefaultRadius()
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
    public function setDefaultRadiusWithStringResultsInInteger()
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
    public function setDefaultRadiusWithBooleanResultsInInteger()
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
    public function getExplicitAllowMapProviderRequestsInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsSetsExplicitAllowGoogleMaps()
    {
        $this->subject->setExplicitAllowMapProviderRequests(true);
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithStringReturnsTrue()
    {
        $this->subject->setExplicitAllowMapProviderRequests('foo bar');
        self::assertTrue($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithZeroReturnsFalse()
    {
        $this->subject->setExplicitAllowMapProviderRequests(0);
        self::assertFalse($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function getExplicitAllowMapProviderRequestsBySessionOnlyInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlySetsExplicitAllowGoogleMapsBySessionOnly()
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(true);
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithStringReturnsTrue()
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly('foo bar');
        self::assertTrue(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithZeroReturnsFalse()
    {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        self::assertFalse(
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function getInfoWindowContentTemplatePathInitiallyReturnsDefaultPath()
    {
        self::assertSame(
            'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathSetsInfoWindowContentTemplatePath()
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
    public function setInfoWindowContentTemplatePathWithIntegerResultsInString()
    {
        $this->subject->setInfoWindowContentTemplatePath(123);
        self::assertSame('123', $this->subject->getInfoWindowContentTemplatePath());
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathWithBooleanResultsInString()
    {
        $this->subject->setInfoWindowContentTemplatePath(true);
        self::assertSame(
            '1',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function getGoogleMapsLibraryInitiallyReturnsEmptyString()
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
    public function setGoogleMapsLibraryWithNoPipeWillNotSetGoogleMapsLibrary()
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
    public function setGoogleMapsLibraryWithNoHttpInFrontWillNotSetGoogleMapsLibrary()
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
    public function setGoogleMapsLibraryWithPipeAndHttpWillSetGoogleMapsLibrary()
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
    public function setGoogleMapsLibraryWithPipeAndHttpsWillSetGoogleMapsLibrary()
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
    public function setGoogleMapsLibraryWithHttpUriAndActivatedHttpsWillSetGoogleMapsLibrary()
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
    public function setGoogleMapsLibraryWithIntegerResultsInEmptyString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary(123);
        self::assertSame('', $this->subject->getGoogleMapsLibrary());
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithBooleanResultsInEmptyString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary(true);
        self::assertSame('', $this->subject->getGoogleMapsLibrary());
    }

    /**
     * @test
     */
    public function getGoogleMapsGeocodeUriInitiallyReturnsPreConfiguredUri()
    {
        self::assertSame(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            $this->subject->getGoogleMapsGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriSetsGoogleMapsGeocodeUri()
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
    public function setGoogleMapsGeocodeUriWithIntegerResultsInString()
    {
        $this->subject->setGoogleMapsGeocodeUri(123);
        self::assertSame('123', $this->subject->getGoogleMapsGeocodeUri());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriWithBooleanResultsInString()
    {
        $this->subject->setGoogleMapsGeocodeUri(true);
        self::assertSame('1', $this->subject->getGoogleMapsGeocodeUri());
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeySetsGoogleMapsJavaScriptApiKey()
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
    public function setGoogleMapsJavaScriptApiKeyWithIntegerResultsInString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey(123);
        self::assertSame('123', $this->subject->getGoogleMapsJavaScriptApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeyWithBooleanResultsInString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey(true);
        self::assertSame('1', $this->subject->getGoogleMapsJavaScriptApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeySetsGoogleMapsGeocodeApiKey()
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
    public function setGoogleMapsGeocodeApiKeyWithIntegerResultsInString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey(123);
        self::assertSame('123', $this->subject->getGoogleMapsGeocodeApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeyWithBooleanResultsInString()
    {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey(true);
        self::assertSame('1', $this->subject->getGoogleMapsGeocodeApiKey());
    }

    /**
     * @test
     */
    public function getOpenStreetMapGeocodeUriInitiallyReturnsPreConfiguredUri()
    {
        self::assertSame(
            'https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1',
            $this->subject->getOpenStreetMapGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriSetsOpenStreetMapGeocodeUri()
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
    public function setOpenStreetMapGeocodeUriWithIntegerResultsInString()
    {
        $this->subject->setOpenStreetMapGeocodeUri(123);
        self::assertSame('123', $this->subject->getOpenStreetMapGeocodeUri());
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriWithBooleanResultsInString()
    {
        $this->subject->setOpenStreetMapGeocodeUri(true);
        self::assertSame('1', $this->subject->getOpenStreetMapGeocodeUri());
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsRedColor()
    {
        self::assertSame(
            '#FF0000',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor()
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
    public function setStrokeColorWithIntegerResultsInString()
    {
        $this->subject->setStrokeColor(123);
        self::assertSame('123', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function setStrokeColorWithBooleanResultsInString()
    {
        $this->subject->setStrokeColor(true);
        self::assertSame('1', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturns0point8()
    {
        self::assertSame(
            0.8,
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity()
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
    public function getStrokeWeightInitiallyReturnsTwo()
    {
        self::assertSame(
            2,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight()
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
    public function setStrokeWeightWithStringResultsInInteger()
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
    public function setStrokeWeightWithBooleanResultsInInteger()
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
    public function getFillColorInitiallyReturnsRedColor()
    {
        self::assertSame(
            '#FF0000',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor()
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
    public function setFillColorWithIntegerResultsInString()
    {
        $this->subject->setFillColor(123);
        self::assertSame('123', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function setFillColorWithBooleanResultsInString()
    {
        $this->subject->setFillColor(true);
        self::assertSame('1', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturns0point35()
    {
        self::assertSame(
            0.35,
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity()
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
    public function getMarkerIconWidthInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth()
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
    public function setMarkerIconWidthWithStringResultsInInteger()
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
    public function setMarkerIconWidthWithBooleanResultsInInteger()
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
    public function getMarkerIconHeightInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight()
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
    public function setMarkerIconHeightWithStringResultsInInteger()
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
    public function setMarkerIconHeightWithBooleanResultsInInteger()
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
    public function getMarkerIconAnchorPosXInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX()
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
    public function setMarkerIconAnchorPosXWithStringResultsInInteger()
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
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger()
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
    public function getMarkerIconAnchorPosYInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY()
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
    public function setMarkerIconAnchorPosYWithStringResultsInInteger()
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
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger()
    {
        $this->subject->setMarkerIconAnchorPosY(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }
}
