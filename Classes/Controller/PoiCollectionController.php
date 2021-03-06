<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends AbstractController
{
    public function showAction(PoiCollection $poiCollection = null): void
    {
        $poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);

        // if uri is empty and a poiCollection is set in FlexForm
        if ($poiCollection === null && !empty($this->settings['poiCollection'])) {
            $poiCollection = $poiCollectionRepository->findByIdentifier((int)$this->settings['poiCollection']);
        }
        if ($poiCollection instanceof PoiCollection) {
            $poiCollections = [$poiCollection];
        } elseif (!empty($this->settings['categories'])) {
            // if no poiCollection could be retrieved, but a category is set
            $poiCollections = $poiCollectionRepository->findPoisByCategories($this->settings['categories']);
            if ($poiCollections->count() === 0) {
                $this->addFlashMessage(
                    'You have configured one or more categories but we can\'t find any PoiCollections which are assigned to these categories.',
                    'No PoiCollections found',
                    FlashMessage::NOTICE
                );
            }
        } else {
            // show all PoiCollections of configured StorageFolder
            $poiCollections = $poiCollectionRepository->findAll();
            if ($poiCollections->count() === 0) {
                $this->addFlashMessage(
                    'You have configured one or more StorageFolders but we can\'t find any PoiCollections which are stored in this folder(s).',
                    'No PoiCollections found',
                    FlashMessage::NOTICE
                );
            }
        }

        $this->view->assign('poiCollections', $poiCollections);
    }

    public function searchAction(Search $search = null): void
    {
        if ($search === null) {
            $search = $this->objectManager->get(Search::class);
        }
        $this->view->assign('search', $search);
    }

    public function listRadiusAction(Search $search): void
    {
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
