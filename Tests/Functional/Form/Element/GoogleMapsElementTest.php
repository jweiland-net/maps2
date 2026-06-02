<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Form\Element;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Form\Element\GoogleMapsElement;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class GoogleMapsElementTest
 */
class GoogleMapsElementTest extends FunctionalTestCase
{
    protected GoogleMapsElement $subject;

    protected array $data = [];

    protected PageRenderer|MockObject $pageRendererMock;

    protected MapHelper|MockObject $mapHelperMock;

    protected ViewFactoryInterface|MockObject $viewFactoryMock;

    protected NodeFactory $nodeFactoryMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'databaseRow' => [
                'uid' => '123',
                'pid' => '321',
                'address' => 'Echterdinger Str. 57, 70794 Filderstadt',
                'collection_type' => [
                    0 => 'Point',
                ],
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [],
                ],
                'itemFormElValue' => 'renderedContent',
            ],
        ];

        $this->mapHelperMock = $this->createMock(MapHelper::class);
        $this->viewFactoryMock = $this->createMock(ViewFactoryInterface::class);
        $this->nodeFactoryMock = $this->createMock(NodeFactory::class);

        $this->subject = new GoogleMapsElement(
            $this->mapHelperMock,
            new ExtConf(),
            $this->viewFactoryMock,
        );
        $this->subject->injectNodeFactory($this->nodeFactoryMock);
        $this->subject->setData($this->data);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapHelperMock,
            $this->viewFactoryMock,
            $this->nodeFactoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function renderWillCreateViewAndAssignVariables(): void
    {
        $record = $this->data['databaseRow'];
        $record['collection_type'] = 'Point';

        $viewMock = $this->createMock(FluidViewAdapter::class);

        $viewMock
            ->expects($this->exactly(2))
            ->method('assign')
            ->willReturnCallback(function (string $key, $value) use ($record, $viewMock) {
                match ($key) {
                    'poiCollection' => $this->assertSame(json_encode($record), $value),
                    'extConf' => true, // Simulates the old $this->anything() behavior
                    default => $this->fail('Unexpected argument passed to assign()'),
                };

                return $viewMock;
            });

        $viewMock
            ->expects($this->atLeastOnce())
            ->method('render')
            ->willReturn('foo');

        $this->viewFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with(new ViewFactoryData(
                templatePathAndFilename: 'EXT:maps2/Resources/Private/Templates/Tca/GoogleMaps.html',
            ))
            ->willReturn($viewMock);

        $this->subject->render();
    }
}
