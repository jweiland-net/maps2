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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    /**
     * @var PageRenderer|MockObject
     */
    protected $pageRendererMock;

    /**
     * @var MapHelper|MockObject
     */
    protected $mapHelperMock;

    /**
     * @var StandaloneView|MockObject
     */
    protected $viewMock;

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

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->pageRendererMock = $this->createMock(PageRenderer::class);
        GeneralUtility::setSingletonInstance(PageRenderer::class, $this->pageRendererMock);

        $this->mapHelperMock = $this->createMock(MapHelper::class);
        GeneralUtility::addInstance(MapHelper::class, $this->mapHelperMock);

        $this->viewMock = $this->createMock(StandaloneView::class);
        GeneralUtility::addInstance(StandaloneView::class, $this->viewMock);

        $this->subject = new OpenStreetMapElement();
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

        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('setTemplatePathAndFilename')
            ->with(
                self::stringContains('Resources/Private/Templates/Tca/OpenStreetMap.html'),
            );
        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('assign')
            ->willReturnMap([
                ['record', json_encode($record), null],
                ['extConf', self::any(), null],
            ]);
        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('render')
            ->willReturn('foo');

        $this->subject->render();
    }
}
