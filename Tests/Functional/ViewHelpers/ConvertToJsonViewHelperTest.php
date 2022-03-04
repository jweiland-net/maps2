<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\ViewHelpers;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Helper\MapHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ConvertToJsonViewHelper
 */
class ConvertToJsonViewHelperTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected PoiCollection $poiCollection;

    protected Category $category;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $extConf = new ExtConf();
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $mapHelper = new MapHelper($extConf);
        GeneralUtility::addInstance(MapHelper::class, $mapHelper);
        $this->poiCollection = new PoiCollection();

        $this->category = new Category();
    }

    protected function tearDown(): void
    {
        unset(
            $this->poiCollection
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWithStringWillJustCallJsonEncode(): void
    {
        $view = new StandaloneView();
        $view->assign('content', 'simpleString');
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {content -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        self::assertStringContainsString(
            '&quot;simpleString&quot;',
            $contentWithJson
        );
    }

    /**
     * @test
     */
    public function renderWithSimpleArrayWillJustCallJsonEncode(): void
    {
        $view = new StandaloneView();
        $view->assign('content', ['foo' => 'bar']);
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {content -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        self::assertStringContainsString(
            '{&quot;foo&quot;:&quot;bar&quot;}',
            $contentWithJson
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionWillSetItToArrayAndConvertItToJson(): void
    {
        $view = new StandaloneView();
        $view->assign('poiCollection', $this->poiCollection);
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollection -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        // a property of PoiCollection should be found in string
        self::assertStringContainsString(
            'address',
            $contentWithJson
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillConvertItToJson(): void
    {
        $view = new StandaloneView();
        $view->assign('poiCollections', [$this->poiCollection]);
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        // a property of PoiCollection should be found in string
        self::assertStringContainsString(
            'address',
            $contentWithJson
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        self::stringStartsWith('[{');
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMaps2MarkerIconsFromCategories(): void
    {
        $poiCollection = $this->poiCollection;
        $poiCollection->addCategory($this->category);

        $view = new StandaloneView();
        $view->assign('poiCollections', [$poiCollection]);
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        self::assertStringNotContainsString(
            'maps2MarkerIcons',
            $contentWithJson
        );
        self::assertStringNotContainsString(
            'parent',
            $contentWithJson
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMarkerIconsFromPoiCollection(): void
    {
        $view = new StandaloneView();
        $view->assign('poiCollections', [$this->poiCollection]);
        $view->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>');

        $contentWithJson = $view->render();

        self::assertStringNotContainsString(
            'markerIcons',
            $contentWithJson
        );
    }
}
