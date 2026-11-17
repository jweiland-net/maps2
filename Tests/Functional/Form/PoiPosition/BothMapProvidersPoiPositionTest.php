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

class BothMapProvidersPoiPositionTest extends AbstractPoiPositionTestCase
{
    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'maps2' => [
                'mapProvider' => 'both',
                'defaultMapProvider' => 'gm',
            ],
        ],
    ];

    #[Test]
    public function formWithoutMapProviderOfElementRendersDefaultMapProvider(): void
    {
        $this->assertMapIsRendered($this->renderForm(), 'gm');
    }

    #[Test]
    public function formRendersMapProviderOfElement(): void
    {
        $content = $this->renderForm(
            [],
            'plugin.tx_form.settings.formDefinitionOverrides.maps2PoiPosition.renderables.0.renderables.0'
            . '.properties.mapProvider = osm',
        );

        $this->assertMapIsRendered($content, 'osm');
        self::assertStringContainsString('OpenStreetMap2.min.js', $content);
    }
}
