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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class OpenStreetMapElementTest
 */
class OpenStreetMapElementTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected OpenStreetMapElement $subject;

    protected array $data = [];

    protected ExtConf $extConf;

    /**
     * @var PageRenderer|ObjectProphecy
     */
    protected $pageRendererProphecy;

    /**
     * @var MapHelper|ObjectProphecy
     */
    protected $mapHelperProphecy;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $viewProphecy;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
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
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->pageRendererProphecy = $this->prophesize(PageRenderer::class);
        GeneralUtility::setSingletonInstance(PageRenderer::class, $this->pageRendererProphecy->reveal());

        $this->mapHelperProphecy = $this->prophesize(MapHelper::class);
        GeneralUtility::addInstance(MapHelper::class, $this->mapHelperProphecy->reveal());

        $this->viewProphecy = $this->prophesize(StandaloneView::class);
        GeneralUtility::addInstance(StandaloneView::class, $this->viewProphecy->reveal());

        $this->subject = new OpenStreetMapElement(
            GeneralUtility::makeInstance(NodeFactory::class),
            $this->data
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->pageRendererProphecy,
            $this->mapHelperProphecy,
            $this->viewProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWillCleanUpCurrentRecord(): void
    {
        $record = $this->data['databaseRow'];
        $record['collection_type'] = 'Point';

        $this->viewProphecy
            ->setTemplatePathAndFilename(
                Argument::containingString('Resources/Private/Templates/Tca/OpenStreetMap.html')
            )
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('record', json_encode($record))
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('extConf', Argument::any())
            ->shouldBeCalled();
        $this->viewProphecy
            ->render()
            ->shouldBeCalled()
            ->willReturn('foo');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWillAddRequireJsModule(): void
    {
        $this->viewProphecy
            ->setTemplatePathAndFilename(
                Argument::containingString('Resources/Private/Templates/Tca/OpenStreetMap.html')
            )
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('record', Argument::any())
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('extConf', Argument::any())
            ->shouldBeCalled();
        $this->viewProphecy
            ->render()
            ->shouldBeCalled()
            ->willReturn('foo');

        self::assertSame(
            [['TYPO3/CMS/Maps2/OpenStreetMapModule' => 'function(OpenStreetMap){OpenStreetMap();}']],
            $this->subject->render()['requireJsModules']
        );
    }
}
