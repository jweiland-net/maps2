<?php
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
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * The main controller to show various kinds of markers on Google Maps
 */
class PoiCollectionController extends AbstractController
{
    /**
     * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * inject poiCollectionRepository
     *
     * @param PoiCollectionRepository $poiCollectionRepository
     *
     * @return void
     */
    public function injectPoiCollectionRepository(PoiCollectionRepository $poiCollectionRepository)
    {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * action show
     *
     * @param PoiCollection $poiCollection PoiCollection from URI has highest priority
     *
     * @return void
     */
    public function showAction(PoiCollection $poiCollection = null)
    {
        // if uri is empty and a poiCollection is set in FlexForm
        if ($poiCollection === null && !empty($this->settings['poiCollection'])) {
            $poiCollection = $this->poiCollectionRepository->findByUid((int)$this->settings['poiCollection']);
        }
        if ($poiCollection instanceof PoiCollection) {
            $this->mapService->setInfoWindow($poiCollection);
            $this->view->assign('poiCollections', $poiCollection);
        } elseif (!empty($this->settings['categories'])) {
            // if no poiCollection could be retrieved, but a category is set
            $poiCollections = $this->poiCollectionRepository->findPoisByCategories($this->settings['categories']);
            foreach ($poiCollections as $poiCollection) {
                $this->mapService->setInfoWindow($poiCollection);
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
     * action search
     * This action shows a form to start a new radius search
     *
     * @param Search $search
     *
     * @return void
     */
    public function searchAction(Search $search = null)
    {
        $this->view->assign('search', $search);
        $this->view->assign('id', $GLOBALS['TSFE']->id);
    }

    /**
     * we have a self-made form. So we have to define allowed properties on our own
     *
     * @return void
     *
     * @throws \Exception
     */
    public function initializeMultipleResultsAction()
    {
        $this->arguments->getArgument('search')
            ->getPropertyMappingConfiguration()
            ->allowProperties('address', 'radius');
    }

    /**
     * action multipleResults
     *
     * after search action it could be that multiple positions are found.
     * With this action the user has the possibility to decide which position he want to see.
     *
     * @param Search $search
     *
     * @return void
     *
     * @throws \Exception
     */
    public function multipleResultsAction(Search $search)
    {
        $radiusResults = $this->geocodeUtility->findPositionByAddress($search->getAddress());
        if ($radiusResults->count() == 1) {
            /* @var $radiusResult \JWeiland\Maps2\Domain\Model\RadiusResult */
            $radiusResult = $radiusResults->current();
            $arguments = [];
            $arguments['latitude'] = $radiusResult->getGeometry()->getLocation()->getLatitude();
            $arguments['longitude'] = $radiusResult->getGeometry()->getLocation()->getLongitude();
            $arguments['radius'] = $search->getRadius();
            $this->forward('listRadius', null, null, $arguments);
        } elseif ($radiusResults->count() > 1) {
            // let the user decide his position
            $this->view->assign('radiusResults', $radiusResults);
            $this->view->assign('newSearch', $search);
        } else {
            // add error message and return to search form
            $this->addFlashMessage(
                'Your position was not found. Please reenter a more detailed address',
                'No result found',
                FlashMessage::NOTICE
            );
            $this->forward('search');
        }
    }

    /**
     * action listRadius
     * Search for POIs within a radius and show them in a list
     * This action was called from the form generated by searchAction
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius
     *
     * @return void
     */
    public function listRadiusAction($latitude, $longitude, $radius)
    {
        $poiCollections = $this->poiCollectionRepository->searchWithinRadius($latitude, $longitude, $radius);
        foreach ($poiCollections as $poiCollection) {
            $this->mapService->setInfoWindow($poiCollection);
        }

        $this->view->assign('latitude', $latitude);
        $this->view->assign('longitude', $longitude);
        $this->view->assign('radius', $radius);
        $this->view->assign('poiCollections', $poiCollections);
    }
}
