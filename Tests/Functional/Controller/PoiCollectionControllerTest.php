<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Controller;

use JWeiland\Maps2\Controller\PoiCollectionController;
use JWeiland\Maps2\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class PoiCollectionControllerTest
 */
class PoiCollectionControllerTest extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;

    protected PoiCollectionController $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_maps2_domain_model_poicollection.csv');

        $this->setUpFrontendSite(1);
        $this->setUpFrontendRootPage(1, ['EXT:maps2/Tests/Functional/Fixtures/TypoScript/setup.typoscript']);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function showActionWillShowPoiCollectionDefinedInPlugin(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content-with-poicollection.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1),
        );

        self::assertSame(200, $response->getStatusCode());

        $content = (string)$response->getBody();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content,
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content,
        );
    }

    /**
     * @test
     */
    public function showActionWithCategoriesButWithoutPoiCollectionsAddsEmptyPois(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content-with-category-uid-2.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1),
        );

        self::assertSame(200, $response->getStatusCode());

        $content = (string)$response->getBody();

        self::assertStringContainsString(
            'data-pois="{}"',
            $content,
        );
    }

    /**
     * Will show only ONE PoiCollection, because the other PoiCollection is in pid 12 (not 1 (TS))
     *
     * @test
     */
    public function showActionWithCategoriesWillShowPoiCollection(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content-with-category-uid-1.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1),
        );

        self::assertSame(200, $response->getStatusCode());

        $content = (string)$response->getBody();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content,
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content,
        );
    }

    /**
     * @test
     */
    public function showActionWithStorageFoldersWithPoiCollections(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content-with-pages.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(1),
        );

        self::assertSame(200, $response->getStatusCode());

        $content = (string)$response->getBody();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content,
        );
        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Stefan&quot;',
            $content,
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content,
        );
    }
}
