<?php
namespace JWeiland\Maps2\Tests\Unit\ViewHelpers;

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
use JWeiland\Maps2\Service\GoogleRequestService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\ViewHelpers\ConvertToJsonViewHelper;
use JWeiland\Maps2\ViewHelpers\Widget\EditPoiViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Class ConvertToJsonViewHelperTest
 */
class ConvertToJsonViewHelperTest extends UnitTestCase
{
    /**
     * @var RenderingContext|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $renderingContext;

    /**
     * @var ViewHelperNode|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $viewHelperNode;

    /**
     * @var ConvertToJsonViewHelper
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->viewHelperNode = $this->prophesize(ViewHelperNode::class);
        $this->renderingContext = $this->prophesize(RenderingContext::class);

        $this->subject = new ConvertToJsonViewHelper();
        $this->subject->setRenderingContext($this->renderingContext->reveal());
        $this->subject->setViewHelperNode($this->viewHelperNode->reveal());
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
        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('simpleString');

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
        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(['foo' => 'bar']);

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
        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(new PoiCollection());

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
        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn([new PoiCollection()]);

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

        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn([$poiCollection]);

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

        $this->viewHelperNode
            ->evaluateChildNodes(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn([$poiCollection]);

        $json = $this->subject->render();

        $this->assertNotContains(
            'markerIcons',
            $json
        );
    }
}
