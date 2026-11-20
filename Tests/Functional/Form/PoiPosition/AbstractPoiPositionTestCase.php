<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Form\PoiPosition;

use JWeiland\Maps2\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Renders a form based on EXT:form with the element "Maps2PoiPosition".
 * The extension settings of maps2 are set by the concrete test cases.
 */
abstract class AbstractPoiPositionTestCase extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;

    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        __DIR__ . '/../../Fixtures/Extensions/maps2_form_test',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->setUpFrontendSite(1);
        $this->setUpFrontendRootPage(1, ['EXT:maps2/Tests/Functional/Fixtures/TypoScript/form.typoscript']);
    }

    /**
     * @param array<string, string> $queryParameters
     */
    protected function renderForm(array $queryParameters = [], string $typoScript = ''): string
    {
        if ($typoScript !== '') {
            $this->addTypoScriptToTemplateRecord(1, $typoScript);
        }

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1)->withQueryParameters($queryParameters),
        );

        return (string)$response->getBody();
    }

    protected function assertMapIsRendered(string $content, string $mapProvider): void
    {
        self::assertStringContainsString('class="maps2 editMarker"', $content);
        self::assertStringContainsString('&quot;mapProvider&quot;:&quot;' . $mapProvider . '&quot;', $content);
        self::assertStringContainsString('&quot;title&quot;:&quot;My company&quot;', $content);
        self::assertStringContainsString('&quot;latitude&quot;:48.1,&quot;longitude&quot;:9.2', $content);
        self::assertStringNotContainsString('maps2-overlay-activate', $content);
    }

    protected function assertHiddenFields(string $content): void
    {
        self::assertStringContainsString(
            'class="latitude-maps2PoiPosition-1-position" type="hidden" '
            . 'name="tx_form_formframework[maps2PoiPosition-1][position][latitude]" value="48.100000"',
            $content,
        );
        self::assertStringContainsString(
            'class="longitude-maps2PoiPosition-1-position" type="hidden" '
            . 'name="tx_form_formframework[maps2PoiPosition-1][position][longitude]" value="9.200000"',
            $content,
        );
    }
}
