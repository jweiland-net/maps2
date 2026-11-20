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
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

class ConsentPoiPositionTest extends AbstractPoiPositionTestCase
{
    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'maps2' => [
                'mapProvider' => 'osm',
                'explicitAllowMapProviderRequests' => '1',
            ],
        ],
    ];

    #[Test]
    public function formWithoutConsentRendersOverlayAndKeepsPosition(): void
    {
        $content = $this->renderForm();

        self::assertStringContainsString('The protection of your data is important for us.', $content);
        self::assertStringContainsString('class="btn btn-secondary maps2-overlay-activate"', $content);
        self::assertMatchesRegularExpression(
            '/formaction="[^"]*tx_maps2_maps2%5BmapProviderRequestsAllowedForMaps2%5D=1[^"]*"/',
            $content,
        );
        self::assertMatchesRegularExpression(
            '/name="tx_form_formframework\[maps2PoiPosition-1\]\[__currentPage\]" value="0"/',
            $content,
        );
        self::assertStringNotContainsString('editMarker', $content);
        self::assertStringNotContainsString('OpenStreetMap2.min.js', $content);
        $this->assertHiddenFields($content);
    }

    #[Test]
    public function formWithConsentParameterRendersMap(): void
    {
        $consentParameter = 'tx_maps2_maps2[mapProviderRequestsAllowedForMaps2]';
        $cacheHash = $this->get(CacheHashCalculator::class)
            ->generateForParameters('id=1&' . $consentParameter . '=1');

        $content = $this->renderForm([$consentParameter => '1', 'cHash' => $cacheHash]);

        $this->assertMapIsRendered($content, 'osm');
        $this->assertHiddenFields($content);
        self::assertStringContainsString('OpenStreetMap2.min.js', $content);
    }
}
