<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Hook\Form;

use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use JWeiland\Maps2\Hook\Form\PoiPositionMapHook;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PoiPositionMapHookTest extends UnitTestCase
{
    private PoiPositionMapConfigurator&MockObject $configurator;

    private FormRuntime&MockObject $formRuntime;

    private PoiPositionMapHook $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->createMock(PoiPositionMapConfigurator::class);
        $this->formRuntime = $this->createMock(FormRuntime::class);
        $this->subject = new PoiPositionMapHook($this->configurator);
    }

    #[Test]
    public function beforeRenderingWithOtherElementTypeDoesNothing(): void
    {
        $element = $this->createMock(FormElementInterface::class);
        $element->method('getType')->willReturn('Text');
        $element->expects(self::never())->method('setRenderingOption');
        $this->configurator->expects(self::never())->method('configure');

        $this->subject->beforeRendering($this->formRuntime, $element);
    }

    #[Test]
    public function beforeRenderingSetsRenderingOptionOfConfigurator(): void
    {
        $value = ['latitude' => '48.1', 'longitude' => '9.2'];
        $request = $this->createMock(Request::class);
        $page = $this->createMock(Page::class);
        $page->method('getIndex')->willReturn(1);

        $element = $this->createMock(FormElementInterface::class);
        $element->method('getType')->willReturn('Maps2PoiPosition');
        $element->method('getIdentifier')->willReturn('position');

        $this->formRuntime->method('offsetGet')->with('position')->willReturn($value);
        $this->formRuntime->method('getRequest')->willReturn($request);
        $this->formRuntime->method('getCurrentPage')->willReturn($page);

        $this->configurator
            ->expects(self::once())
            ->method('configure')
            ->with($element, $value, $request, 1)
            ->willReturn(['mapProvider' => 'osm']);

        $element
            ->expects(self::once())
            ->method('setRenderingOption')
            ->with('maps2PoiPosition', ['mapProvider' => 'osm']);

        $this->subject->beforeRendering($this->formRuntime, $element);
    }
}
