<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\ViewHelpers;

use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class ConvertToJsonViewHelper
 */
class ConvertToJsonViewHelperTest extends FunctionalTestCase
{
    protected PoiCollection $poiCollection;

    protected Category $category;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->poiCollection = new PoiCollection();

        $this->category = new Category();
    }

    protected function tearDown(): void
    {
        unset(
            $this->poiCollection,
        );

        parent::tearDown();
    }

    #[Test]
    public function renderWithStringWillJustCallJsonEncode(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {content -> m:convertToJson()}
            </html>
        ');
        self::assertStringContainsString(
            '&quot;simpleString&quot;',
            (new TemplateView($context))->assign('content', 'simpleString')->render(),
        );
    }

    #[Test]
    public function renderWithSimpleArrayWillJustCallJsonEncode(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {content -> m:convertToJson()}
            </html>
        ');
        self::assertStringContainsString(
            '{&quot;foo&quot;:&quot;bar&quot;}',
            (new TemplateView($context))->assign('content', ['foo' => 'bar'])->render(),
        );
    }

    #[Test]
    public function renderWithPoiCollectionWillSetItToArrayAndConvertItToJson(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollection -> m:convertToJson()}
            </html>
        ');

        // a property of PoiCollection should be found in string
        self::assertStringContainsString(
            'address',
            (new TemplateView($context))->assign('poiCollection', $this->poiCollection)->render(),
        );
    }

    #[Test]
    public function renderWithPoiCollectionsWillConvertItToJson(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>
        ');

        // a property of PoiCollection should be found in string
        self::assertStringContainsString(
            'address',
            (new TemplateView($context))->assign('poiCollections', [$this->poiCollection])->render(),
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        self::stringStartsWith('[{');
    }

    #[Test]
    public function renderWithPoiCollectionsWillRemoveMaps2MarkerIconsFromCategories(): void
    {
        $poiCollection = $this->poiCollection;
        $poiCollection->addCategory($this->category);

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>
        ');

        self::assertStringNotContainsString(
            'maps2MarkerIcons',
            (new TemplateView($context))->assign('poiCollections', [$poiCollection])->render(),
        );

        self::assertStringNotContainsString(
            'parent',
            (new TemplateView($context))->assign('poiCollections', [$poiCollection])->render(),
        );
    }

    #[Test]
    public function renderWithPoiCollectionsWillRemoveMarkerIconsFromPoiCollection(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('
            <html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:m="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {poiCollections -> m:convertToJson()}
            </html>
        ');

        self::assertStringNotContainsString(
            'markerIcons',
            (new TemplateView($context))->assign('poiCollections', [$this->poiCollection])->render(),
        );
    }
}
