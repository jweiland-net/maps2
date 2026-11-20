<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Form\PoiPosition;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PoiPositionMapConfiguratorTest extends UnitTestCase
{
    private const POSITION = ['latitude' => '48.123456', 'longitude' => '9.654321'];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest('https://example.com/', 'GET');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    #[Test]
    public function configureReturnsHiddenFieldValuesAndMapData(): void
    {
        $result = $this->createSubject(new ExtConf(mapProvider: 'osm'))->configure(
            $this->createElement(['markerTitle' => 'My company', 'zoom' => 12, 'height' => '50vh']),
            self::POSITION,
            $this->createRequest([
                'mapTile' => 'https://${s}.example.com/${z}/${x}/${y}.png',
                'googleMapsJavaScriptApiKey' => 'ts-key',
            ]),
            2,
        );

        self::assertSame('48.123456', $result['latitude']);
        self::assertSame('9.654321', $result['longitude']);
        self::assertSame('form-1-position', $result['mapIdentifier']);
        self::assertSame('osm', $result['mapProvider']);
        self::assertSame('ts-key', $result['googleMapsJavaScriptApiKey']);
        self::assertTrue($result['isRequestToMapProviderAllowed']);
        self::assertSame(2, $result['currentPageIndex']);

        $environment = json_decode($result['environment'], true);
        self::assertSame('https://{s}.example.com/{z}/{x}/{y}.png', $environment['settings']['mapTile']);
        self::assertSame(12, $environment['settings']['zoom']);
        self::assertSame('50vh', $environment['settings']['mapHeight']);
        self::assertSame('100%', $environment['settings']['mapWidth']);
        self::assertTrue($environment['settings']['forceZoom']);
        self::assertSame(['uid' => 'form-1-position'], $environment['contentRecord']);
        self::assertArrayNotHasKey('googleMapsJavaScriptApiKey', $environment['settings']);
        self::assertSame(['defaultLatitude', 'defaultLongitude'], array_keys($environment['extConf']));

        self::assertSame(
            [[
                'uid' => 0,
                'collectionType' => 'Point',
                'title' => 'My company',
                'latitude' => 48.123456,
                'longitude' => 9.654321,
                'categories' => [],
            ]],
            json_decode($result['pois'], true),
        );
    }

    #[Test]
    public function configureWithoutPositionUsesDefaultPositionForMarkerOnly(): void
    {
        $result = $this->createSubject(new ExtConf(defaultLatitude: 52.5, defaultLongitude: 13.4))->configure(
            $this->createElement([]),
            null,
            $this->createRequest([]),
            0,
        );

        self::assertSame('', $result['latitude']);
        self::assertSame('', $result['longitude']);

        $poi = json_decode($result['pois'], true)[0];
        self::assertSame(52.5, $poi['latitude']);
        self::assertSame(13.4, $poi['longitude']);
    }

    #[Test]
    public function configureWithPositionOutOfRangeUsesDefaultPosition(): void
    {
        $result = $this->createSubject(new ExtConf())->configure(
            $this->createElement([]),
            ['latitude' => '91', 'longitude' => '9'],
            $this->createRequest([]),
            0,
        );

        self::assertSame('', $result['latitude']);
    }

    #[Test]
    public function configureWithoutConsentDisallowsRequestsToMapProvider(): void
    {
        $result = $this->createSubject(new ExtConf(explicitAllowMapProviderRequests: true))->configure(
            $this->createElement([]),
            self::POSITION,
            $this->createRequest([]),
            0,
        );

        self::assertFalse($result['isRequestToMapProviderAllowed']);
        self::assertSame('48.123456', $result['latitude']);
    }

    #[Test]
    public function configureWithConsentParameterAllowsRequestsToMapProvider(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/', 'POST'))
            ->withQueryParams(['tx_maps2_maps2' => ['mapProviderRequestsAllowedForMaps2' => '1']]);

        $result = $this->createSubject(new ExtConf(explicitAllowMapProviderRequests: true))->configure(
            $this->createElement([]),
            self::POSITION,
            $this->createRequest([]),
            0,
        );

        self::assertTrue($result['isRequestToMapProviderAllowed']);
    }

    #[Test]
    public function configureFallsBackToApiKeyOfExtensionSettings(): void
    {
        $result = $this->createSubject(new ExtConf(mapProvider: 'gm', googleMapsJavaScriptApiKey: 'ext-conf-key'))->configure(
            $this->createElement([]),
            self::POSITION,
            $this->createRequest([]),
            0,
        );

        self::assertSame('ext-conf-key', $result['googleMapsJavaScriptApiKey']);
    }

    #[Test]
    public function configureWithoutMapTileUsesDefaultMapTileOfElement(): void
    {
        $result = $this->createSubject(new ExtConf(mapProvider: 'osm'))->configure(
            $this->createElement([
                'defaultMapTile' => 'https://${s}.default.example.com/${z}/${x}/${y}.png',
                'defaultMapTileAttribution' => 'Default tiles',
            ]),
            self::POSITION,
            new ServerRequest('https://example.com/', 'GET'),
            0,
        );

        $settings = json_decode($result['environment'], true)['settings'];
        self::assertSame('https://{s}.default.example.com/{z}/{x}/{y}.png', $settings['mapTile']);
        self::assertSame('Default tiles', $settings['mapTileAttribution']);
    }

    #[Test]
    public function configurePrefersMapTileOfTypoScript(): void
    {
        $result = $this->createSubject(new ExtConf(mapProvider: 'osm'))->configure(
            $this->createElement(['defaultMapTile' => 'https://default.example.com/{z}/{x}/{y}.png']),
            self::POSITION,
            $this->createRequest(['mapTile' => 'https://ts.example.com/{z}/{x}/{y}.png', 'mapTileAttribution' => 'TS tiles']),
            0,
        );

        $settings = json_decode($result['environment'], true)['settings'];
        self::assertSame('https://ts.example.com/{z}/{x}/{y}.png', $settings['mapTile']);
        self::assertSame('TS tiles', $settings['mapTileAttribution']);
    }

    public static function mapProviderDataProvider(): array
    {
        return [
            'both allowed, property gm' => ['both', 'osm', 'gm', 'gm'],
            'both allowed, property osm' => ['both', 'gm', 'osm', 'osm'],
            'both allowed, empty property' => ['both', 'osm', '', 'osm'],
            'both allowed, invalid property' => ['both', 'gm', 'foo', 'gm'],
            'strict osm, property gm' => ['osm', 'gm', 'gm', 'osm'],
            'strict gm, property osm' => ['gm', 'gm', 'osm', 'gm'],
        ];
    }

    #[Test]
    #[DataProvider('mapProviderDataProvider')]
    public function getMapProviderRespectsExtensionSettings(
        string $mapProvider,
        string $defaultMapProvider,
        string $property,
        string $expected,
    ): void {
        $subject = $this->createSubject(new ExtConf(mapProvider: $mapProvider, defaultMapProvider: $defaultMapProvider));

        self::assertSame($expected, $subject->getMapProvider($property));
    }

    public static function zoomDataProvider(): array
    {
        return [
            'int' => [10, 10],
            'numeric string' => ['8', 8],
            'zero' => [0, 0],
            'too high' => [21, 15],
            'negative' => [-1, 15],
            'not numeric' => ['big', 15],
            'null' => [null, 15],
        ];
    }

    #[Test]
    #[DataProvider('zoomDataProvider')]
    public function getZoomReturnsValidZoom(mixed $zoom, int $expected): void
    {
        self::assertSame($expected, $this->createSubject(new ExtConf())->getZoom($zoom));
    }

    public static function heightDataProvider(): array
    {
        return [
            'int' => [300, '300'],
            'pixels' => ['300px', '300px'],
            'viewport height' => ['50vh', '50vh'],
            'percent' => ['100%', '100%'],
            'decimal em' => ['20.5em', '20.5em'],
            'css injection' => ['300px; background: red', '400'],
            'unknown unit' => ['3cm', '400'],
            'array' => [['300'], '400'],
            'null' => [null, '400'],
        ];
    }

    #[Test]
    #[DataProvider('heightDataProvider')]
    public function getHeightReturnsValidHeight(mixed $height, string $expected): void
    {
        self::assertSame($expected, $this->createSubject(new ExtConf())->getHeight($height));
    }

    private function createSubject(ExtConf $extConf): PoiPositionMapConfigurator
    {
        return new PoiPositionMapConfigurator($extConf, new MapHelper($extConf), new TypoScriptService());
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function createElement(array $properties): FormElementInterface
    {
        $element = $this->createMock(FormElementInterface::class);
        $element->method('getProperties')->willReturn($properties);
        $element->method('getUniqueIdentifier')->willReturn('form-1-position');

        return $element;
    }

    /**
     * @param array<string, string> $settings TypoScript settings of plugin.tx_maps2
     */
    private function createRequest(array $settings): ServerRequest
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray(['plugin.' => ['tx_maps2.' => ['settings.' => $settings]]]);

        return (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }
}
