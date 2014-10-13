<?php
namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EditPoiController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var \JWeiland\Maps2\Domain\Model\PoiCollection
	 */
	protected $poiCollection;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * @var \JWeiland\Maps2\Configuration\ExtConf
	 * @inject
	 */
	protected $extConf;

	protected $mapOptions = array(
		'zoom' => 12,
		'mapTypeId' => 'google.maps.MapTypeId.HYBRID',
		'panControl' => 1,
		'zoomControl' => 1,
		'mapTypeControl' => 1,
		'scaleControl' => 1,
		'streetViewControl' => 1,
		'overviewMapControl' => 1,
	);





	public function initializeAction() {
		$this->poiCollection = $this->widgetConfiguration['poiCollection'];
		ArrayUtility::mergeRecursiveWithOverrule($this->mapOptions, $this->getMapOptions(), TRUE);
	}

	/**
	 * index action
	 *
	 * @return string
	 */
	public function indexAction() {
		$this->view->assign('extConf', ObjectAccess::getGettableProperties($this->extConf));
		$this->view->assign('poiCollection', $this->poiCollection);
		$this->view->assign('mapOptions', $this->mapOptions);
		$this->view->assign('width', $this->widgetConfiguration['width']);
		$this->view->assign('height', $this->widgetConfiguration['height']);
		$this->view->assign('prepend', $this->widgetConfiguration['prepend']);
		$this->view->assign('id', $this->widgetConfiguration['id']);
	}

	/**
	 * if some values are set to FALSE in template, they were set to NULL
	 * This method returns this values back to FALSE
	 *
	 * @return array
	 */
	public function getMapOptions() {
		foreach ($this->widgetConfiguration['mapOptions'] as $key => $value) {
			if (empty($this->widgetConfiguration['mapOptions'][$key])) {
				$this->widgetConfiguration['mapOptions'][$key] = 0;
			} else $this->widgetConfiguration['mapOptions'][$key] = $value;
		}
		return $this->widgetConfiguration['mapOptions'];
	}

}