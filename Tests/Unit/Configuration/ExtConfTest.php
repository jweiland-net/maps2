<?php
namespace JWeiland\Maps2\Tests\Unit\Configuration;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new ExtConf();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getMapProviderInitiallyReturnsBothAsString() {
        $this->assertSame(
            'both',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function setMapProviderSetsMapProvider() {
        $this->subject->setMapProvider('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function setMapProviderWithIntegerResultsInString() {
        $this->subject->setMapProvider(123);
        $this->assertSame('123', $this->subject->getMapProvider());
    }

    /**
     * @test
     */
    public function setMapProviderWithBooleanResultsInString() {
        $this->subject->setMapProvider(TRUE);
        $this->assertSame('1', $this->subject->getMapProvider());
    }

    /**
     * @test
     */
    public function getDefaultMapProviderInitiallyReturnsGoogleMapsAsString() {
        $this->assertSame(
            'gm',
            $this->subject->getDefaultMapProvider()
        );
    }

    /**
     * @test
     */
    public function setDefaultMapProviderSetsDefaultMapProvider() {
        $this->subject->setDefaultMapProvider('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getDefaultMapProvider()
        );
    }

    /**
     * @test
     */
    public function setDefaultMapProviderWithIntegerResultsInString() {
        $this->subject->setDefaultMapProvider(123);
        $this->assertSame('123', $this->subject->getDefaultMapProvider());
    }

    /**
     * @test
     */
    public function setDefaultMapProviderWithBooleanResultsInString() {
        $this->subject->setDefaultMapProvider(TRUE);
        $this->assertSame('1', $this->subject->getDefaultMapProvider());
    }

    /**
     * @test
     */
    public function getDefaultCountryInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getDefaultCountry()
        );
    }

    /**
     * @test
     */
    public function setDefaultCountrySetsDefaultCountry() {
        $this->subject->setDefaultCountry('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getDefaultCountry()
        );
    }

    /**
     * @test
     */
    public function setDefaultCountryWithIntegerResultsInString() {
        $this->subject->setDefaultCountry(123);
        $this->assertSame('123', $this->subject->getDefaultCountry());
    }

    /**
     * @test
     */
    public function setDefaultCountryWithBooleanResultsInString() {
        $this->subject->setDefaultCountry(TRUE);
        $this->assertSame('1', $this->subject->getDefaultCountry());
    }

    /**
     * @test
     */
    public function getDefaultLatitudeInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getDefaultLatitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLatitudeSetsDefaultLatitude() {
        $this->subject->setDefaultLatitude(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getDefaultLatitude()
        );
    }

    /**
     * @test
     */
    public function getDefaultLongitudeInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getDefaultLongitude()
        );
    }

    /**
     * @test
     */
    public function setDefaultLongitudeSetsDefaultLongitude() {
        $this->subject->setDefaultLongitude(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getDefaultLongitude()
        );
    }

    /**
     * @test
     */
    public function getDefaultRadiusInitiallyReturns250() {
        $this->assertSame(
            250,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusSetsDefaultRadius() {
        $this->subject->setDefaultRadius(123456);

        $this->assertSame(
            123456,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusWithStringResultsInInteger() {
        $this->subject->setDefaultRadius('123Test');

        $this->assertSame(
            123,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function setDefaultRadiusWithBooleanResultsInInteger() {
        $this->subject->setDefaultRadius(true);

        $this->assertSame(
            1,
            $this->subject->getDefaultRadius()
        );
    }

    /**
     * @test
     */
    public function getExplicitAllowMapProviderRequestsInitiallyReturnsFalse() {
        $this->assertSame(
            false,
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsSetsExplicitAllowGoogleMaps() {
        $this->subject->setExplicitAllowMapProviderRequests(true);
        $this->assertSame(
            true,
            $this->subject->getExplicitAllowMapProviderRequests()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithStringReturnsTrue() {
        $this->subject->setExplicitAllowMapProviderRequests('foo bar');
        $this->assertTrue($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsWithZeroReturnsFalse() {
        $this->subject->setExplicitAllowMapProviderRequests(0);
        $this->assertFalse($this->subject->getExplicitAllowMapProviderRequests());
    }

    /**
     * @test
     */
    public function getExplicitAllowMapProviderRequestsBySessionOnlyInitiallyReturnsFalse() {
        $this->assertSame(
            false,
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlySetsExplicitAllowGoogleMapsBySessionOnly() {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(true);
        $this->assertSame(
            true,
            $this->subject->getExplicitAllowMapProviderRequestsBySessionOnly()
        );
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithStringReturnsTrue() {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly('foo bar');
        $this->assertTrue($this->subject->getExplicitAllowMapProviderRequestsBySessionOnly());
    }

    /**
     * @test
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithZeroReturnsFalse() {
        $this->subject->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->assertFalse($this->subject->getExplicitAllowMapProviderRequestsBySessionOnly());
    }

    /**
     * @test
     */
    public function getInfoWindowContentTemplatePathInitiallyReturnsDefaultPath() {
        $this->assertSame(
            'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathSetsInfoWindowContentTemplatePath() {
        $this->subject->setInfoWindowContentTemplatePath('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getInfoWindowContentTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathWithIntegerResultsInString() {
        $this->subject->setInfoWindowContentTemplatePath(123);
        $this->assertSame('123', $this->subject->getInfoWindowContentTemplatePath());
    }

    /**
     * @test
     */
    public function setInfoWindowContentTemplatePathWithBooleanResultsInString() {
        $this->subject->setInfoWindowContentTemplatePath(true);
        $this->assertSame('1', $this->subject->getInfoWindowContentTemplatePath());
    }

    /**
     * @test
     */
    public function getAllowMapTemplatePathInitiallyReturnsEmptyString() {
        $this->assertSame(
            'EXT:maps2/Resources/Private/Templates/AllowMapForm.html',
            $this->subject->getAllowMapTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setAllowMapTemplatePathSetsAllowMapTemplatePath() {
        $this->subject->setAllowMapTemplatePath('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getAllowMapTemplatePath()
        );
    }

    /**
     * @test
     */
    public function setAllowMapTemplatePathWithIntegerResultsInString() {
        $this->subject->setAllowMapTemplatePath(123);
        $this->assertSame('123', $this->subject->getAllowMapTemplatePath());
    }

    /**
     * @test
     */
    public function setAllowMapTemplatePathWithBooleanResultsInString() {
        $this->subject->setAllowMapTemplatePath(true);
        $this->assertSame('1', $this->subject->getAllowMapTemplatePath());
    }

    /**
     * @test
     */
    public function getGoogleMapsLibraryInitiallyReturnsEmptyString() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->assertSame(
            'https://maps.googleapis.com/maps/api/js?key=myApiKey&libraries=places',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithNoPipeWillNotSetGoogleMapsLibrary() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('foo bar');

        $this->assertSame(
            '',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithNoHttpInFrontWillNotSetGoogleMapsLibrary() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('www.domain.de/api=|&mobile=1');

        $this->assertSame(
            '',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithPipeAndHttpWillSetGoogleMapsLibrary() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('http://www.domain.de/api=|&mobile=1');

        $this->assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithPipeAndHttpsWillSetGoogleMapsLibrary() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('https://www.domain.de/api=|&mobile=1');

        $this->assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithHttpUriAndActivatedHttpsWillSetGoogleMapsLibrary() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary('http://www.domain.de/api=|&mobile=1');

        $this->assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $this->subject->getGoogleMapsLibrary()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithIntegerResultsInEmptyString() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary(123);
        $this->assertSame('', $this->subject->getGoogleMapsLibrary());
    }

    /**
     * @test
     */
    public function setGoogleMapsLibraryWithBooleanResultsInEmptyString() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsLibrary(true);
        $this->assertSame('', $this->subject->getGoogleMapsLibrary());
    }

    /**
     * @test
     */
    public function getGoogleMapsGeocodeUriInitiallyReturnsPreConfiguredUri() {
        $this->assertSame(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            $this->subject->getGoogleMapsGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriSetsGoogleMapsGeocodeUri() {
        $this->subject->setGoogleMapsGeocodeUri('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getGoogleMapsGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriWithIntegerResultsInString() {
        $this->subject->setGoogleMapsGeocodeUri(123);
        $this->assertSame('123', $this->subject->getGoogleMapsGeocodeUri());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeUriWithBooleanResultsInString() {
        $this->subject->setGoogleMapsGeocodeUri(TRUE);
        $this->assertSame('1', $this->subject->getGoogleMapsGeocodeUri());
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeySetsGoogleMapsJavaScriptApiKey() {
        $this->subject->setGoogleMapsJavaScriptApiKey('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getGoogleMapsJavaScriptApiKey()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeyWithIntegerResultsInString() {
        $this->subject->setGoogleMapsJavaScriptApiKey(123);
        $this->assertSame('123', $this->subject->getGoogleMapsJavaScriptApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsJavaScriptApiKeyWithBooleanResultsInString() {
        $this->subject->setGoogleMapsJavaScriptApiKey(true);
        $this->assertSame('1', $this->subject->getGoogleMapsJavaScriptApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeySetsGoogleMapsGeocodeApiKey() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getGoogleMapsGeocodeApiKey()
        );
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeyWithIntegerResultsInString() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey(123);
        $this->assertSame('123', $this->subject->getGoogleMapsGeocodeApiKey());
    }

    /**
     * @test
     */
    public function setGoogleMapsGeocodeApiKeyWithBooleanResultsInString() {
        $this->subject->setGoogleMapsJavaScriptApiKey('myApiKey');
        $this->subject->setGoogleMapsGeocodeApiKey(true);
        $this->assertSame('1', $this->subject->getGoogleMapsGeocodeApiKey());
    }

    /**
     * @test
     */
    public function getOpenStreetMapGeocodeUriInitiallyReturnsPreConfiguredUri() {
        $this->assertSame(
            'https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1',
            $this->subject->getOpenStreetMapGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriSetsOpenStreetMapGeocodeUri() {
        $this->subject->setOpenStreetMapGeocodeUri('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getOpenStreetMapGeocodeUri()
        );
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriWithIntegerResultsInString() {
        $this->subject->setOpenStreetMapGeocodeUri(123);
        $this->assertSame('123', $this->subject->getOpenStreetMapGeocodeUri());
    }

    /**
     * @test
     */
    public function setOpenStreetMapGeocodeUriWithBooleanResultsInString() {
        $this->subject->setOpenStreetMapGeocodeUri(TRUE);
        $this->assertSame('1', $this->subject->getOpenStreetMapGeocodeUri());
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsRedColor() {
        $this->assertSame(
            '#FF0000',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor() {
        $this->subject->setStrokeColor('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorWithIntegerResultsInString() {
        $this->subject->setStrokeColor(123);
        $this->assertSame('123', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function setStrokeColorWithBooleanResultsInString() {
        $this->subject->setStrokeColor(true);
        $this->assertSame('1', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturns0point8() {
        $this->assertSame(
            0.8,
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity() {
        $this->subject->setStrokeOpacity(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function getStrokeWeightInitiallyReturnsTwo() {
        $this->assertSame(
            2,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight() {
        $this->subject->setStrokeWeight(123456);

        $this->assertSame(
            123456,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightWithStringResultsInInteger() {
        $this->subject->setStrokeWeight('123Test');

        $this->assertSame(
            123,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightWithBooleanResultsInInteger() {
        $this->subject->setStrokeWeight(true);

        $this->assertSame(
            1,
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function getFillColorInitiallyReturnsRedColor() {
        $this->assertSame(
            '#FF0000',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor() {
        $this->subject->setFillColor('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorWithIntegerResultsInString() {
        $this->subject->setFillColor(123);
        $this->assertSame('123', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function setFillColorWithBooleanResultsInString() {
        $this->subject->setFillColor(true);
        $this->assertSame('1', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturns0point35() {
        $this->assertSame(
            0.35,
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity() {
        $this->subject->setFillOpacity(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconWidthInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth() {
        $this->subject->setMarkerIconWidth(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithStringResultsInInteger() {
        $this->subject->setMarkerIconWidth('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithBooleanResultsInInteger() {
        $this->subject->setMarkerIconWidth(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconHeightInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight() {
        $this->subject->setMarkerIconHeight(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithStringResultsInInteger() {
        $this->subject->setMarkerIconHeight('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithBooleanResultsInInteger() {
        $this->subject->setMarkerIconHeight(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosXInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX() {
        $this->subject->setMarkerIconAnchorPosX(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithStringResultsInInteger() {
        $this->subject->setMarkerIconAnchorPosX('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger() {
        $this->subject->setMarkerIconAnchorPosX(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosYInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY() {
        $this->subject->setMarkerIconAnchorPosY(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithStringResultsInInteger() {
        $this->subject->setMarkerIconAnchorPosY('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger() {
        $this->subject->setMarkerIconAnchorPosY(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }
}
