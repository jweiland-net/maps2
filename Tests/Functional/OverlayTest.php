<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional;

use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Overlay
 */
class OverlayTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/tt_content-with-poicollection.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/tx_maps2_domain_model_poicollection.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                __DIR__ . '/Fixtures/TypoScript/setup.typoscript',
                __DIR__ . '/Fixtures/TypoScript/activate-plugin-overlay.typoscript',
            ]
        );
    }

    /**
     * @test
     */
    public function overlayWillAskForConsent(): void
    {
        $response = $this->getFrontendResponse(1);

        self::assertStringContainsString(
            'The protection of your data is important for us',
            $response->getContent()
        );
    }
}
