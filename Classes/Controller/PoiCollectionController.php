<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Controller;

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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends AbstractController
{
    /**
     * Action show
     *
     * @param PoiCollection $poiCollection PoiCollection from URI has highest priority
     * @throws \Exception
     */
    public function showAction(PoiCollection $poiCollection = null)
    {
        $mapService = GeneralUtility::makeInstance(MapService::class);
        $poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);

        // if uri is empty and a poiCollection is set in FlexForm
        if ($poiCollection === null && !empty($this->settings['poiCollection'])) {
            $poiCollection = $poiCollectionRepository->findByUid((int)$this->settings['poiCollection']);
        }
        if ($poiCollection instanceof PoiCollection) {
            $mapService->setInfoWindow($poiCollection);
            $this->view->assign('poiCollections', $poiCollection);
        } elseif (!empty($this->settings['categories'])) {
            // if no poiCollection could be retrieved, but a category is set
            $poiCollections = $poiCollectionRepository->findPoisByCategories($this->settings['categories']);
            foreach ($poiCollections as $poiCollection) {
                $mapService->setInfoWindow($poiCollection);
            }
            $this->view->assign('poiCollections', $poiCollections);
        }
        if ($poiCollection === null) {
            $this->addFlashMessage(
                'There are currently no PoiCollections defined. Either in URI nor in Plugin configuration',
                'No POIs found',
                FlashMessage::NOTICE
            );
        }
    }

    /**
     * This action shows a form to start a new radius search
     *
     * @param Search $search
     */
    public function searchAction(Search $search = null)
    {
        if ($search === null) {
            $search = $this->objectManager->get(Search::class);
        }
        $this->view->assign('search', $search);
    }

    /**
     * action listRadius
     * Search for POIs within a radius and show them in a list
     *
     * @param Search $search
     * @throws \Exception
     */
    public function listRadiusAction(Search $search)
    {
        $mapService = GeneralUtility::makeInstance(MapService::class);
        $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);

        $this->view->assign('search', $search);
        $position = $geoCodeService->getFirstFoundPositionByAddress($search->getAddress());
        if ($position instanceof Position) {
            $poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
            $poiCollections = $poiCollectionRepository->searchWithinRadius(
                $position->getLatitude(),
                $position->getLongitude(),
                $search->getRadius()
            );
            foreach ($poiCollections as $poiCollection) {
                $mapService->setInfoWindow($poiCollection);
            }

            $this->view->assign('poiCollections', $poiCollections);
        } else {
            $this->addFlashMessage(
                'No position with this address found',
                'Address not found',
                AbstractMessage::ERROR
            );
        }
    }
}
