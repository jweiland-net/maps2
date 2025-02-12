<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional;

use JWeiland\Maps2\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Overlay
 */
class OverlayTest extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content-with-poicollection.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_maps2_domain_model_poicollection.csv');
        $this->setUpFrontendSite(1);
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:maps2/Tests/Functional/Fixtures/TypoScript/setup.typoscript',
                'EXT:maps2/Tests/Functional/Fixtures/TypoScript/activate-plugin-overlay.typoscript',
            ],
        );
    }

    #[Test]
    public function overlayWillAskForConsent(): void
    {
        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1),
        );

        self::assertStringContainsString(
            'The protection of your data is important for us',
            (string)$response->getBody(),
        );
    }
}
