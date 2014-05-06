<?php
namespace JWeiland\Maps2\Tca;

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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConfigurationMap {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \JWeiland\Maps2\Configuration\ExtConf
	 */
	protected $extConf;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected $view;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
	 */
	protected $poiCollectionRepository;





	/**
	 * initializes this class
	 */
	public function init() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->extConf = $this->objectManager->get('JWeiland\\Maps2\\Configuration\\ExtConf');
		$this->hashService = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Security\\Cryptography\\HashService');
		$this->view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$this->pageRenderer = $this->objectManager->get('TYPO3\\CMS\\Core\\Page\\PageRenderer');
		$this->poiCollectionRepository = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Repository\\PoiCollectionRepository');
	}

	/**
	 * Renders the Google map.
	 *
	 * @param array $PA
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $pObj
	 * @return string
	 */
	public function render(array &$PA, \TYPO3\CMS\Backend\Form\FormEngine $pObj) {
		$this->init();

		// add Google Maps API
		$this->pageRenderer->addJsLibrary('maps2GoogleMapsApi', $this->extConf->getGoogleMapsLibrary(), 'text/javascript', false, true, '', true);

		return $this->getMapHtml($this->getConfiguration($PA));
	}

	/**
	 * get configuration array from PA array
	 *
	 * @param array $PA
	 * @return array
	 */
	public function getConfiguration(array &$PA) {
		$config = array();

		// get poi collection model
		$uid = (int) $PA['row']['uid'];
		$poiCollection = $this->poiCollectionRepository->findByUid($uid);
		if ($poiCollection instanceof \JWeiland\Maps2\Domain\Model\PoiCollection) {
			// set map center
			$config['latitude'] = ($poiCollection->getLatitude()) ? $poiCollection->getLatitude() : $this->extConf->getDefaultLatitude();
			$config['longitude'] = ($poiCollection->getLongitude()) ? $poiCollection->getLongitude() : $this->extConf->getDefaultLongitude();
			switch ($poiCollection->getCollectionType()) {
				case 'Route':
				case 'Area':
					// set pois
					/** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
					foreach ($poiCollection->getPois() as $poi) {
						$latLng['latitude'] = $poi->getLatitude();
						$latLng['longitude'] = $poi->getLongitude();
						$config['pois'][] = $latLng;
					}
					if (!isset($config['pois'])) $config['pois'] = array();
				break;
				case 'Radius':
					$config['radius'] = ($poiCollection->getRadius()) ? $poiCollection->getRadius() : $this->extConf->getDefaultRadius();
					$config['radius'] = $poiCollection->getRadius();
					break;
				default:
					break;
			}

			$config['address'] =  $PA['row']['address'];
			$config['collectionType'] =  $PA['row']['collection_type'];
			$config['uid'] =  $uid;
			$config['TYPO3_SITE_URL'] =  GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

			$hashArray['uid'] = $uid;
			$hashArray['collectionType'] = $PA['row']['collection_type'];
			$config['hash'] = $this->hashService->generateHmac(serialize($hashArray));
		}
		return $config;
	}

	/**
	 * get parsed content from template
	 *
	 * @param array $config
	 * @return string
	 */
	public function getMapHtml(array $config) {
		$this->view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('maps2') . 'Resources/Private/Templates/Tca/ConfigurationMap.html');
		$this->view->assign('config', json_encode($config));
		$this->view->assign('design', json_encode(ObjectAccess::getGettableProperties($this->extConf)));
		$content = $this->view->render() . CHR(10);
		$content .= file_get_contents(ExtensionManagementUtility::extPath('maps2') . 'Resources/Private/Templates/Tca/ConfigurationMapFor' . ucfirst($config['collectionType']) . '.html');

		return $content;
	}

}