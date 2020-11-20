<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Controller;

use JWeiland\Maps2\Controller\PoiCollectionController;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Class PoiCollectionControllerTest
 */
class PoiCollectionControllerTest extends UnitTestCase
{
    /**
     * @var PoiCollectionController|\PHPUnit_Framework_MockObject_MockObject|AccessibleMockObjectInterface
     */
    protected $subject;

    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManagerProphecy;

    /**
     * @var PoiCollectionRepository|ObjectProphecy
     */
    protected $poiCollectionRepositoryProphecy;

    /**
     * @var MapService|ObjectProphecy
     */
    protected $mapServiceProphecy;

    /**
     * @var ConfigurationManager|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var ControllerContext|ObjectProphecy
     */
    protected $controllerContextProphecy;

    /**
     * @var TemplateView|ObjectProphecy
     */
    protected $viewProphecy;

    public function setUp()
    {
        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $this->poiCollectionRepositoryProphecy = $this->prophesize(PoiCollectionRepository::class);
        $this->mapServiceProphecy = $this->prophesize(MapService::class);
        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManager::class);
        $this->controllerContextProphecy = $this->prophesize(ControllerContext::class);

        $this->objectManagerProphecy
            ->get(PoiCollectionRepository::class)
            ->shouldBeCalled()
            ->willReturn($this->poiCollectionRepositoryProphecy->reveal());

        GeneralUtility::addInstance(MapService::class, $this->mapServiceProphecy->reveal());

        $this->subject = $this->getAccessibleMock(PoiCollectionController::class, ['dummy']);
        $this->subject->_set('settings', []);
        $this->subject->_set('objectManager', $this->objectManagerProphecy->reveal());
        $this->subject->_set('view', $this->viewProphecy->reveal());
        $this->subject->_set('configurationManager', $this->configurationManagerProphecy->reveal());
        $this->subject->_set('controllerContext', $this->controllerContextProphecy->reveal());
    }

    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function showActionShowsPoiCollectionFromUri()
    {
        $poiCollection = new PoiCollection();
        $this->mapServiceProphecy
            ->setInfoWindow($poiCollection)
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('poiCollections', [$poiCollection])
            ->shouldBeCalled();

        $this->subject->showAction($poiCollection);
    }

    /**
     * @test
     */
    public function showActionShowsPoiCollectionByItsIdentifier()
    {
        $this->subject->_set(
            'settings',
            [
                'poiCollection' => 123
            ]
        );

        $poiCollection = new PoiCollection();
        $this->poiCollectionRepositoryProphecy
            ->findByIdentifier(123)
            ->shouldBeCalled()
            ->willReturn($poiCollection);
        $this->viewProphecy
            ->assign('poiCollections', [$poiCollection])
            ->shouldBeCalled();

        $this->subject->showAction();
    }

    /**
     * @test
     */
    public function showActionWithCategoriesButWithoutPoiCollectionsAddsFlashMessage()
    {
        $this->subject->_set(
            'settings',
            [
                'categories' => '12,13'
            ]
        );

        /** @var FlashMessage|ObjectProphecy $flashMessageProphecy */
        $flashMessageProphecy = $this->prophesize(FlashMessage::class);
        GeneralUtility::addInstance(FlashMessage::class, $flashMessageProphecy->reveal());

        /** @var FlashMessageQueue|ObjectProphecy $flashMessageQueueProphecy */
        $flashMessageQueueProphecy = $this->prophesize(FlashMessageQueue::class);
        $flashMessageQueueProphecy
            ->enqueue($flashMessageProphecy->reveal())
            ->shouldBeCalled($flashMessageProphecy->reveal());

        $this->controllerContextProphecy
            ->getFlashMessageQueue()
            ->shouldBeCalled()
            ->willReturn($flashMessageQueueProphecy->reveal());

        /** @var QueryResultInterface|ObjectProphecy $queryResultProphecy */
        $queryResultProphecy = $this->prophesize(QueryResultInterface::class);
        $queryResultProphecy
            ->count()
            ->shouldBeCalled()
            ->willReturn(0);
        $queryResultProphecy
            ->rewind()
            ->shouldBeCalled();
        $queryResultProphecy
            ->valid()
            ->shouldBeCalled();

        $this->poiCollectionRepositoryProphecy
            ->findPoisByCategories('12,13')
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->viewProphecy
            ->assign('poiCollections', $queryResultProphecy->reveal())
            ->shouldBeCalled();

        $this->subject->showAction();
    }

    /**
     * @test
     */
    public function showActionWithCategoriesWithPoiCollections()
    {
        $this->subject->_set(
            'settings',
            [
                'categories' => '12,13'
            ]
        );

        /** @var QueryResultInterface|ObjectProphecy $queryResultProphecy */
        $queryResultProphecy = $this->prophesize(QueryResultInterface::class);
        $queryResultProphecy
            ->count()
            ->shouldBeCalled()
            ->willReturn(2);
        $queryResultProphecy
            ->rewind()
            ->shouldBeCalled();
        $queryResultProphecy
            ->valid()
            ->shouldBeCalled();

        $this->poiCollectionRepositoryProphecy
            ->findPoisByCategories('12,13')
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->viewProphecy
            ->assign('poiCollections', $queryResultProphecy->reveal())
            ->shouldBeCalled();

        $this->subject->showAction();
    }

    /**
     * @test
     */
    public function showActionWithStorageFoldersButWithoutPoiCollectionsAddsFlashMessage()
    {
        /** @var FlashMessage|ObjectProphecy $flashMessageProphecy */
        $flashMessageProphecy = $this->prophesize(FlashMessage::class);
        GeneralUtility::addInstance(FlashMessage::class, $flashMessageProphecy->reveal());

        /** @var FlashMessageQueue|ObjectProphecy $flashMessageQueueProphecy */
        $flashMessageQueueProphecy = $this->prophesize(FlashMessageQueue::class);
        $flashMessageQueueProphecy
            ->enqueue($flashMessageProphecy->reveal())
            ->shouldBeCalled($flashMessageProphecy->reveal());

        $this->controllerContextProphecy
            ->getFlashMessageQueue()
            ->shouldBeCalled()
            ->willReturn($flashMessageQueueProphecy->reveal());

        /** @var QueryResultInterface|ObjectProphecy $queryResultProphecy */
        $queryResultProphecy = $this->prophesize(QueryResultInterface::class);
        $queryResultProphecy
            ->count()
            ->shouldBeCalled()
            ->willReturn(0);
        $queryResultProphecy
            ->rewind()
            ->shouldBeCalled();
        $queryResultProphecy
            ->valid()
            ->shouldBeCalled();

        $this->poiCollectionRepositoryProphecy
            ->findAll()
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->viewProphecy
            ->assign('poiCollections', $queryResultProphecy->reveal())
            ->shouldBeCalled();

        $this->subject->showAction();
    }

    /**
     * @test
     */
    public function showActionWithStorageFoldersWithPoiCollections()
    {
        /** @var QueryResultInterface|ObjectProphecy $queryResultProphecy */
        $queryResultProphecy = $this->prophesize(QueryResultInterface::class);
        $queryResultProphecy
            ->count()
            ->shouldBeCalled()
            ->willReturn(2);
        $queryResultProphecy
            ->rewind()
            ->shouldBeCalled();
        $queryResultProphecy
            ->valid()
            ->shouldBeCalled();

        $this->poiCollectionRepositoryProphecy
            ->findAll()
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->viewProphecy
            ->assign('poiCollections', $queryResultProphecy->reveal())
            ->shouldBeCalled();

        $this->subject->showAction();
    }
}
