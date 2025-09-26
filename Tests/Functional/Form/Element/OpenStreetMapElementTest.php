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
use JWeiland\Maps2\Form\Element\OpenStreetMapElement;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class OpenStreetMapElementTest
 */
class OpenStreetMapElementTest extends FunctionalTestCase
{
    protected OpenStreetMapElement $subject;

    protected array $data = [];

    protected ExtConf $extConf;

    protected PageRenderer|MockObject $pageRendererMock;

    protected MapHelper|MockObject $mapHelperMock;

    protected StandaloneView|MockObject $viewMock;

    protected ViewFactoryInterface $viewFactoryMock;

    protected NodeFactory $nodeFactoryMock;

    protected array $coreExtensionsToLoad = [
        'extensionmanager',
        'reactions',
    ];

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/maps2',
        'jweiland/events2',
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

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->pageRendererMock = $this->createMock(PageRenderer::class);
        GeneralUtility::setSingletonInstance(PageRenderer::class, $this->pageRendererMock);

        $this->mapHelperMock = $this->createMock(MapHelper::class);
        GeneralUtility::addInstance(MapHelper::class, $this->mapHelperMock);

        $this->viewMock = $this->createMock(ViewInterface::class);
        GeneralUtility::addInstance(ViewInterface::class, $this->viewMock);

        $this->viewFactoryMock = $this->createMock(ViewFactoryInterface::class);
        GeneralUtility::addInstance(ViewFactoryInterface::class, $this->viewFactoryMock);

        $this->nodeFactoryMock = $this->createMock(NodeFactory::class);
        GeneralUtility::addInstance(NodeFactory::class, $this->nodeFactoryMock);

        $this->subject = new OpenStreetMapElement(
            $this->extConf,
            $this->pageRendererMock,
            $this->mapHelperMock,
            $this->viewFactoryMock,
            $this->nodeFactoryMock,
        );
        $this->subject->setData($this->data);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->pageRendererMock,
            $this->mapHelperMock,
            $this->viewMock,
            $this->viewFactoryMock,
            $this->nodeFactoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function renderWillCleanUpCurrentRecord(): void
    {
        $this->viewFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->viewMock);

        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('render')
            ->willReturn('foo');

        $this->subject->render();
    }
}
