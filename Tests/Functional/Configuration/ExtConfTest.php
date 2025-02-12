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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class ExtConfTest
 */
class ExtConfTest extends FunctionalTestCase
{
    public ExtensionConfiguration|MockObject $extensionConfigurationMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->extensionConfigurationMock,
        );
    }

    #[Test]
    public function getMapProviderInitiallyReturnsBothAsString(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'both',
            $subject->getMapProvider(),
        );
    }

    #[Test]
    public function setMapProviderSetsMapProvider(): void
    {
        $config = [
            'mapProvider' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getMapProvider(),
        );
    }

    #[Test]
    public function getDefaultMapProviderInitiallyReturnsGoogleMapsAsString(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'gm',
            $subject->getDefaultMapProvider(),
        );
    }

    #[Test]
    public function setDefaultMapProviderSetsDefaultMapProvider(): void
    {
        $config = [
            'defaultMapProvider' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getDefaultMapProvider(),
        );
    }

    #[Test]
    public function getDefaultCountryInitiallyReturnsEmptyString(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            '',
            $subject->getDefaultCountry(),
        );
    }

    #[Test]
    public function setDefaultCountrySetsDefaultCountry(): void
    {
        $config = [
            'defaultCountry' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getDefaultCountry(),
        );
    }

    #[Test]
    public function getDefaultLatitudeInitiallyReturnsZero(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            0.0,
            $subject->getDefaultLatitude(),
        );
    }

    #[Test]
    public function setDefaultLatitudeSetsDefaultLatitude(): void
    {
        $config = [
            'defaultLatitude' => 1234.56,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            1234.56,
            $subject->getDefaultLatitude(),
        );
    }

    #[Test]
    public function getDefaultLongitudeInitiallyReturnsZero(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            0.0,
            $subject->getDefaultLongitude(),
        );
    }

    #[Test]
    public function setDefaultLongitudeSetsDefaultLongitude(): void
    {
        $config = [
            'defaultLongitude' => 1234.56,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            1234.56,
            $subject->getDefaultLongitude(),
        );
    }

    #[Test]
    public function getDefaultRadiusInitiallyReturns250(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            250,
            $subject->getDefaultRadius(),
        );
    }

    #[Test]
    public function setDefaultRadiusSetsDefaultRadius(): void
    {
        $config = [
            'defaultRadius' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getDefaultRadius(),
        );
    }

    #[Test]
    public function setDefaultRadiusWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'defaultRadius' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getDefaultRadius(),
        );
    }

    #[Test]
    public function setDefaultRadiusWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'defaultRadius' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getDefaultRadius(),
        );
    }

    #[Test]
    public function getExplicitAllowMapProviderRequestsInitiallyReturnsFalse(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertFalse(
            $subject->getExplicitAllowMapProviderRequests(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsSetsExplicitAllowGoogleMaps(): void
    {
        $config = [
            'explicitAllowMapProviderRequests' => true,
        ];
        $subject = new ExtConf(...$config);

        self::assertTrue(
            $subject->getExplicitAllowMapProviderRequests(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsWithStringReturnsTrue(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'explicitAllowMapProviderRequests' => 'foo bar',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertTrue(
            $subject->getExplicitAllowMapProviderRequests(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsWithZeroReturnsFalse(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'explicitAllowMapProviderRequests' => 0,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertFalse(
            $subject->getExplicitAllowMapProviderRequests(),
        );
    }

    #[Test]
    public function getExplicitAllowMapProviderRequestsBySessionOnlyInitiallyReturnsFalse(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertFalse(
            $subject->getExplicitAllowMapProviderRequestsBySessionOnly(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsBySessionOnlySetsExplicitAllowGoogleMapsBySessionOnly(): void
    {
        $config = [
            'explicitAllowMapProviderRequestsBySessionOnly' => true,
        ];
        $subject = new ExtConf(...$config);

        self::assertTrue(
            $subject->getExplicitAllowMapProviderRequestsBySessionOnly(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithStringReturnsTrue(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'explicitAllowMapProviderRequestsBySessionOnly' => 'foo bar',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertTrue(
            $subject->getExplicitAllowMapProviderRequestsBySessionOnly(),
        );
    }

    #[Test]
    public function setExplicitAllowMapProviderRequestsBySessionOnlyWithZeroReturnsFalse(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'explicitAllowMapProviderRequestsBySessionOnly' => 0,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertFalse(
            $subject->getExplicitAllowMapProviderRequestsBySessionOnly(),
        );
    }

    #[Test]
    public function getGoogleMapsLibraryInitiallyReturnsEmptyString(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://maps.googleapis.com/maps/api/js?key=myApiKey&libraries=places',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function setGoogleMapsLibraryWithNoPipeWillNotSetGoogleMapsLibrary(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsLibrary' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            '',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function setGoogleMapsLibraryWithNoHttpInFrontWillNotSetGoogleMapsLibrary(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsLibrary' => 'www.domain.de/api=|&mobile=1',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            '',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function setGoogleMapsLibraryWithPipeAndHttpWillSetGoogleMapsLibrary(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsLibrary' => 'http://www.domain.de/api=|&mobile=1',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function setGoogleMapsLibraryWithPipeAndHttpsWillSetGoogleMapsLibrary(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsLibrary' => 'https://www.domain.de/api=|&mobile=1',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function setGoogleMapsLibraryWithHttpUriAndActivatedHttpsWillSetGoogleMapsLibrary(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsLibrary' => 'http://www.domain.de/api=|&mobile=1',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://www.domain.de/api=myApiKey&mobile=1',
            $subject->getGoogleMapsLibrary(),
        );
    }

    #[Test]
    public function getGoogleMapsGeocodeUriInitiallyReturnsPreConfiguredUri(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            $subject->getGoogleMapsGeocodeUri(),
        );
    }

    #[Test]
    public function setGoogleMapsGeocodeUriSetsGoogleMapsGeocodeUri(): void
    {
        $config = [
            'googleMapsGeocodeUri' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getGoogleMapsGeocodeUri(),
        );
    }

    #[Test]
    public function setGoogleMapsJavaScriptApiKeySetsGoogleMapsJavaScriptApiKey(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getGoogleMapsJavaScriptApiKey(),
        );
    }

    #[Test]
    public function setGoogleMapsGeocodeApiKeySetsGoogleMapsGeocodeApiKey(): void
    {
        $config = [
            'googleMapsJavaScriptApiKey' => 'myApiKey',
            'googleMapsGeocodeApiKey' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getGoogleMapsGeocodeApiKey(),
        );
    }

    #[Test]
    public function getOpenStreetMapGeocodeUriInitiallyReturnsPreConfiguredUri(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'https://nominatim.openstreetmap.org/search?q=%s&format=json&addressdetails=1',
            $subject->getOpenStreetMapGeocodeUri(),
        );
    }

    #[Test]
    public function setOpenStreetMapGeocodeUriSetsOpenStreetMapGeocodeUri(): void
    {
        $config = [
            'openStreetMapGeocodeUri' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getOpenStreetMapGeocodeUri(),
        );
    }

    #[Test]
    public function getStrokeColorInitiallyReturnsRedColor(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            '#FF0000',
            $subject->getStrokeColor(),
        );
    }

    #[Test]
    public function setStrokeColorSetsStrokeColor(): void
    {
        $config = [
            'strokeColor' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getStrokeColor(),
        );
    }

    #[Test]
    public function getStrokeOpacityInitiallyReturns0point8(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            0.8,
            $subject->getStrokeOpacity(),
        );
    }

    #[Test]
    public function setStrokeOpacitySetsStrokeOpacity(): void
    {
        $config = [
            'strokeOpacity' => 1234.56,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            1234.56,
            $subject->getStrokeOpacity(),
        );
    }

    #[Test]
    public function getStrokeWeightInitiallyReturnsTwo(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            2,
            $subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function setStrokeWeightSetsStrokeWeight(): void
    {
        $config = [
            'strokeWeight' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function setStrokeWeightWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'strokeWeight' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function setStrokeWeightWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'strokeWeight' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function getFillColorInitiallyReturnsRedColor(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            '#FF0000',
            $subject->getFillColor(),
        );
    }

    #[Test]
    public function setFillColorSetsFillColor(): void
    {
        $config = [
            'fillColor' => 'foo bar',
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            'foo bar',
            $subject->getFillColor(),
        );
    }

    #[Test]
    public function getFillOpacityInitiallyReturns0point35(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            0.35,
            $subject->getFillOpacity(),
        );
    }

    #[Test]
    public function setFillOpacitySetsFillOpacity(): void
    {
        $config = [
            'fillOpacity' => 1234.56,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            1234.56,
            $subject->getFillOpacity(),
        );
    }

    #[Test]
    public function getMarkerIconWidthInitiallyReturns25(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            25,
            $subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthSetsMarkerIconWidth(): void
    {
        $config = [
            'markerIconWidth' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconWidth' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconWidth' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function getMarkerIconHeightInitiallyReturns40(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            40,
            $subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightSetsMarkerIconHeight(): void
    {
        $config = [
            'markerIconHeight' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconHeight' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconHeight' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function getMarkerIconAnchorPosXInitiallyReturns13(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            13,
            $subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX(): void
    {
        $config = [
            'markerIconAnchorPosX' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconAnchorPosX' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconAnchorPosX' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function getMarkerIconAnchorPosYInitiallyReturns40(): void
    {
        $config = [];
        $subject = new ExtConf(...$config);

        self::assertSame(
            40,
            $subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY(): void
    {
        $config = [
            'markerIconAnchorPosY' => 123456,
        ];
        $subject = new ExtConf(...$config);

        self::assertSame(
            123456,
            $subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWithStringResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconAnchorPosY' => '123Test',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            123,
            $subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger(): void
    {
        $this->extensionConfigurationMock
            ->expects(self::once())
            ->method('get')
            ->with('maps2')
            ->willReturn([
                'markerIconAnchorPosY' => true,
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            1,
            $subject->getMarkerIconAnchorPosY(),
        );
    }
}
