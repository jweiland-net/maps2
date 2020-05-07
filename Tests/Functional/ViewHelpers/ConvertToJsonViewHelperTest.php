<?php
namespace JWeiland\Maps2\Tests\Functional\ViewHelpers;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\ViewHelpers\ConvertToJsonViewHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Class ConvertToJsonViewHelper
 */
class ConvertToJsonViewHelperTest extends FunctionalTestCase
{
    /**
     * @var RenderingContext|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $renderingContext;

    /**
     * @var ConvertToJsonViewHelper
     */
    protected $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->renderingContext = $this->prophesize(RenderingContext::class);

        $this->subject = new ConvertToJsonViewHelper();
        $this->subject->setRenderingContext($this->renderingContext->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWithStringWillJustCallJsonEncode()
    {
        $this->subject->setRenderChildrenClosure(
            function() {
                return 'simpleString';
            }
        );

        $this->assertSame(
            '&quot;simpleString&quot;',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithSimpleArrayWillJustCallJsonEncode()
    {
        $this->subject->setRenderChildrenClosure(
            function() {
                return ['foo' => 'bar'];
            }
        );

        $this->assertSame(
            '{&quot;foo&quot;:&quot;bar&quot;}',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionWillSetItToArrayAndConvertItToJson()
    {
        $this->subject->setRenderChildrenClosure(
            function() {
                return new PoiCollection();
            }
        );

        GeneralUtility::setSingletonInstance(ExtConf::class, new ExtConf([]));

        $json = $this->subject->render();

        // a property of PoiCollection should be found in string
        $this->assertContains(
            'address',
            $json
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        $this->stringStartsWith('[{');
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillConvertItToJson()
    {
        $this->subject->setRenderChildrenClosure(
            function() {
                return [new PoiCollection()];
            }
        );

        $json = $this->subject->render();

        // a property of PoiCollection should be found in string
        $this->assertContains(
            'address',
            $json
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        $this->stringStartsWith('[{');
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMaps2MarkerIconsFromCategories()
    {
        $poiCollection = new PoiCollection();
        $poiCollection->addCategory(new Category());

        $this->subject->setRenderChildrenClosure(
            function() use ($poiCollection) {
                return [$poiCollection];
            }
        );

        $json = $this->subject->render();

        $this->assertNotContains(
            'maps2MarkerIcons',
            $json
        );
        $this->assertNotContains(
            'parent',
            $json
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMarkerIconsFromPoiCollection()
    {
        $poiCollection = new PoiCollection();

        $this->subject->setRenderChildrenClosure(
            function() use ($poiCollection) {
                return [$poiCollection];
            }
        );

        $json = $this->subject->render();

        $this->assertNotContains(
            'markerIcons',
            $json
        );
    }
}
