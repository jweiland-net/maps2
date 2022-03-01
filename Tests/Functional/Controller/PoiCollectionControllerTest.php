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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class PoiCollectionControllerTest
 */
class PoiCollectionControllerTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected PoiCollectionController $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_category.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_maps2_domain_model_poicollection.xml');
        $this->setUpFrontendRootPage(1, [__DIR__ . '/../Fixtures/TypoScript/setup.typoscript']);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function showActionWillShowPoiCollectionDefinedInPlugin(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/tt_content-with-poicollection.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $content = $response->getContent();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content
        );
    }

    /**
     * @test
     */
    public function showActionWithCategoriesButWithoutPoiCollectionsAddsEmptyPois(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/tt_content-with-empty-category.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $content = $response->getContent();

        self::assertStringContainsString(
            'data-pois="{}"',
            $content
        );
    }

    /**
     * Will show only ONE PoiCollection, because the other PoiCollection is in pid 12 (not 1 (TS))
     *
     * @test
     */
    public function showActionWithCategoriesWillShowPoiCollection(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/tt_content-with-category.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $content = $response->getContent();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content
        );
    }

    /**
     * @test
     */
    public function showActionWithStorageFoldersWithPoiCollections(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/tt_content-with-pages.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $content = $response->getContent();

        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Jochen&quot;',
            $content
        );
        self::assertStringContainsString(
            '&quot;title&quot;:&quot;Stefan&quot;',
            $content
        );

        self::assertStringNotContainsString(
            'data-pois="{}"',
            $content
        );
    }
}
