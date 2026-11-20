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

class GoogleMapsPoiPositionTest extends AbstractPoiPositionTestCase
{
    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'maps2' => [
                'mapProvider' => 'gm',
            ],
        ],
    ];

    #[Test]
    public function formRendersGoogleMapsWithDraggableMarker(): void
    {
        $content = $this->renderForm();

        $this->assertMapIsRendered($content, 'gm');
        $this->assertHiddenFields($content);
        self::assertStringContainsString('&quot;mapHeight&quot;:&quot;300&quot;', $content);
        self::assertStringContainsString('&quot;zoom&quot;:12', $content);
        self::assertStringContainsString('maps.googleapis.com/maps/api/js?key=functional-test-key', $content);
        self::assertStringContainsString('GoogleMaps2.min.js', $content);
        self::assertStringNotContainsString('leaflet.min.js', $content);
    }

    #[Test]
    public function mapProviderOfElementIsIgnoredIfOnlyGoogleMapsIsAllowed(): void
    {
        $content = $this->renderForm(
            [],
            'plugin.tx_form.settings.formDefinitionOverrides.maps2PoiPosition.renderables.0.renderables.0'
            . '.properties.mapProvider = osm',
        );

        $this->assertMapIsRendered($content, 'gm');
    }
}
