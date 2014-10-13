<?php
namespace JWeiland\Maps2\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <sfroemken@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PoiCollectionController extends \JWeiland\Maps2\Controller\AbstractController {

	/**
	 * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
	 * @inject
	 */
	protected $poiCollectionRepository;

	/**
	 * @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator
	 * @inject
	 */
	protected $cacheHash;





	/**
	 * initialize view
	 * add some global vars to view
	 *
	 * @return void
	 */
	public function initializeView() {
		$this->view->assign('extConf', ObjectAccess::getGettableProperties($this->extConf));
		$this->view->assign('id', $GLOBALS['TSFE']->id);
	}

	/**
	 * action show
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection
	 * @return void
	 */
	public function showAction(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection = NULL) {
		// overwrite poiCollection if it was set in FlexForm
		if (!empty($this->settings['poiCollection'])) {
			$poiCollection = $this->poiCollectionRepository->findByUid((int)$this->settings['poiCollection']);
		};
		if ($poiCollection instanceof \JWeiland\Maps2\Domain\Model\PoiCollection) {
			$this->view->assign('poiCollection', $poiCollection);
		};
	}

	/**
	 * action all pois of a specific category
	 *
	 * @return void
	 */
	public function showPoisOfCategoryAction() {
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
	public function searchAction(\JWeiland\Maps2\Domain\Model\Search $search = NULL) {
		$this->view->assign('search', $search);
		$parameters = array();
		$parameters['id'] = $GLOBALS['TSFE']->id;
		$parameters['tx_maps2_searchwithinradius']['controller'] = 'PoiCollection';
		$parameters['tx_maps2_searchwithinradius']['action'] = 'checkForMultiple';
		$cachHashArray = $this->cacheHash->getRelevantParameters(GeneralUtility::implodeArrayForUrl('', $parameters));
		$this->view->assign('cHash', $this->cacheHash->calculateCacheHash($cachHashArray));
	}

	/**
	 * we have a self-made form. So we have to define allowed properties on our own
	 *
	 * @return void
	 */
	public function initializeCheckForMultipleAction() {
		$this->arguments->getArgument('search')->getPropertyMappingConfiguration()->allowProperties('address', 'radius');
	}

	/**
	 * action checkForMultiple
	 * after the search action it could be that multiple positions were found.
	 * With this action the user has the possibility to decide which position to use.
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Search $search
	 * @return void
	 */
	public function checkForMultipleAction(\JWeiland\Maps2\Domain\Model\Search $search) {
		$jSon = GeneralUtility::getUrl('http://maps.googleapis.com/maps/api/geocode/json?address=' . $this->updateAddressForUri($search->getAddress()) . '&sensor=false');
		$response = json_decode($jSon, TRUE);
		$radiusResults = $this->dataMapper->mapObjectStorage('JWeiland\\Maps2\\Domain\\Model\\RadiusResult', $response['results']);

		if ($radiusResults->count() == 1) {
			/* @var $radiusResult \JWeiland\Maps2\Domain\Model\RadiusResult */
			$radiusResult = $radiusResults->current();
			$arguments = array();
			$arguments['latitude'] = $radiusResult->getGeometry()->getLocation()->getLatitude();
			$arguments['longitude'] = $radiusResult->getGeometry()->getLocation()->getLongitude();
			$arguments['radius'] = $search->getRadius();
			$this->forward('listRadius', NULL, NULL, $arguments);
		} elseif ($radiusResults->count() > 1) {
			// let the user decide his position
			$this->view->assign('radiusResults', $radiusResults);
			$this->view->assign('newSearch', $search);
		} else {
			// add error message and return to search form
			// @ToDo
			$this->flashMessageContainer->add('Your position was not found. Please reenter a more detailed address', 'no result found', \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE);
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
	public function listRadiusAction($latitude, $longitude, $radius) {
		$poiCollections = $this->poiCollectionRepository->searchWithinRadius($latitude, $longitude, $radius);

		$this->view->assign('latitude', $latitude);
		$this->view->assign('longitude', $longitude);
		$this->view->assign('radius', $radius);
		$this->view->assign('poiCollections', $poiCollections);
	}

}