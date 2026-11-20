<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Form\PoiPosition;

use PHPUnit\Framework\Attributes\Test;

class OpenStreetMapPoiPositionTest extends AbstractPoiPositionTestCase
{
    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'maps2' => [
                'mapProvider' => 'osm',
            ],
        ],
    ];

    #[Test]
    public function formRendersOpenStreetMapWithDraggableMarker(): void
    {
        $content = $this->renderForm();

        $this->assertMapIsRendered($content, 'osm');
        $this->assertHiddenFields($content);
        self::assertStringContainsString('&quot;mapTile&quot;:&quot;https://tile.example.com/{z}/{x}/{y}.png&quot;', $content);
        self::assertStringContainsString('leaflet.min.js', $content);
        self::assertStringContainsString('OpenStreetMap2.min.js', $content);
        self::assertStringContainsString('Leaflet.css', $content);
        self::assertStringNotContainsString('GoogleMaps2.min.js', $content);
    }

    #[Test]
    public function partialWithAssetsCanBeOverridden(): void
    {
        $content = $this->renderForm(
            [],
            'plugin.tx_form.settings.formDefinitionOverrides.maps2PoiPosition.renderingOptions'
            . '.partialRootPaths.100 = EXT:maps2/Tests/Functional/Fixtures/FormFramework/Partials/',
        );

        $this->assertMapIsRendered($content, 'osm');
        self::assertStringContainsString('src="https://cdn.example.com/osm/custom-map.js"', $content);
        self::assertStringNotContainsString('OpenStreetMap2.min.js', $content);
        self::assertStringNotContainsString('leaflet.min.js', $content);
    }
}
