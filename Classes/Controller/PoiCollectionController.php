<?php
namespace JWeiland\Maps2\Controller;

/**
 * This file is part of the TYPO3 CMS project.
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PoiCollectionController
 *
 * @category Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class PoiCollectionController extends AbstractController
{

    /**
     * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator
     */
    protected $cacheHashCalculator;

    /**
     * inject poiCollectionRepository
     *
     * @param \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository
     * @return void
     */
    public function injectPoiCollectionRepository(
        \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository
    ) {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * inject cacheHashCalculator
     *
     * @param \TYPO3\CMS\Frontend\Page\CacheHashCalculator $cacheHashCalculator
     * @return void
     */
    public function injectCacheHashCalculator(
        \TYPO3\CMS\Frontend\Page\CacheHashCalculator $cacheHashCalculator
    ) {
        $this->cacheHashCalculator = $cacheHashCalculator;
    }

    /**
     * action show
     *
     * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection
     * @return void
     */
    public function showAction(
        \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection = null
    ) {
        // overwrite poiCollection if it was set in FlexForm
        if (!empty($this->settings['poiCollection'])) {
            $poiCollection = $this->poiCollectionRepository->findByUid((int)$this->settings['poiCollection']);
        };
        if ($poiCollection instanceof PoiCollection) {
            $this->view->assign('poiCollection', $poiCollection);
        };
    }

    /**
     * action all pois of a specific category
     *
     * @return void
     */
    public function showPoisOfCategoryAction()
    {
        if (!empty($this->settings['categories'])) {
            $poiCollections = $this->poiCollectionRepository->findPoisByCategories($this->settings['categories']);
            if (!empty($poiCollections)) {
                $this->view->assign('poiCollections', $poiCollections);
            };
        };
    }

    /**
     * action search
     * This action shows a form to start a new radius search
     *
     * @param \JWeiland\Maps2\Domain\Model\Search $search
     * @return void
     */
    public function searchAction(\JWeiland\Maps2\Domain\Model\Search $search = null)
    {
        $this->view->assign('search', $search);
        $parameters = array();
        $parameters['id'] = $GLOBALS['TSFE']->id;
        $parameters['tx_maps2_searchwithinradius']['controller'] = 'PoiCollection';
        $parameters['tx_maps2_searchwithinradius']['action'] = 'checkForMultiple';
        $cachHashArray = $this->cacheHashCalculator->getRelevantParameters(
            GeneralUtility::implodeArrayForUrl('', $parameters)
        );
        $this->view->assign('cHash', $this->cacheHashCalculator->calculateCacheHash($cachHashArray));
    }

    /**
     * we have a self-made form. So we have to define allowed properties on our own
     *
     * @return void
     */
    public function initializeCheckForMultipleAction()
    {
        $this->arguments->getArgument('search')
            ->getPropertyMappingConfiguration()
            ->allowProperties('address', 'radius');
    }

    /**
     * action checkForMultiple
     * after the search action it could be that multiple positions were found.
     * With this action the user has the possibility to decide which position to use.
     *
     * @param \JWeiland\Maps2\Domain\Model\Search $search
     * @return void
     */
    public function checkForMultipleAction(\JWeiland\Maps2\Domain\Model\Search $search)
    {
        $jSon = GeneralUtility::getUrl(
            'http://maps.googleapis.com/maps/api/geocode/json?address=' .
            $this->updateAddressForUri($search->getAddress()) . '&sensor=false'
        );
        $response = json_decode($jSon, true);
        $radiusResults = $this->dataMapper->mapObjectStorage(
            'JWeiland\\Maps2\\Domain\\Model\\RadiusResult',
            $response['results']
        );

        if ($radiusResults->count() == 1) {
            /* @var $radiusResult \JWeiland\Maps2\Domain\Model\RadiusResult */
            $radiusResult = $radiusResults->current();
            $arguments = array();
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
            // @ToDo
            $this->flashMessageContainer->add(
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
     * @param integer $radius
     * @return void
     */
    public function listRadiusAction($latitude, $longitude, $radius)
    {
        $poiCollections = $this->poiCollectionRepository->searchWithinRadius($latitude, $longitude, $radius);

        $this->view->assign('latitude', $latitude);
        $this->view->assign('longitude', $longitude);
        $this->view->assign('radius', $radius);
        $this->view->assign('poiCollections', $poiCollections);
    }
}
