<?php
namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Stefan Froemken <projects@jweiland.net>, jweiland.net
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
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PointController extends AbstractWidgetController {

	/**
	 * @var \JWeiland\Maps2\Configuration\ExtConf
	 */
	protected $extConf;

	/**
	 * inject extConf
	 *
	 * @param \JWeiland\Maps2\Configuration\ExtConf $extConf
	 * @return void
	 */
	public function injectExtConf(\JWeiland\Maps2\Configuration\ExtConf $extConf) {
		$this->extConf = $extConf;
	}

	/**
	 * @var array
	 */
	protected $center = array();

	/**
	 * @var array
	 */
	protected $mapOptions = array(
		'zoom' => 12,
		'mapTypeId' => 'google.maps.MapTypeId.ROADMAP',
		'panControl' => 1,
		'zoomControl' => 1,
		'mapTypeControl' => 1,
		'scaleControl' => 1,
		'streetViewControl' => 1,
		'overviewMapControl' => 1,
	);

	/**
	 * @var int
	 */
	protected $width = 400;

	/**
	 * @var int
	 */
	protected $height = 300;

	/**
	 * initializes the index action
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->center = array(
			'latitude' => $this->extConf->getDefaultLatitude(),
			'longitude' => $this->extConf->getDefaultLongitude()
		);
		ArrayUtility::mergeRecursiveWithOverrule($this->center, $this->widgetConfiguration['center'], TRUE);
		ArrayUtility::mergeRecursiveWithOverrule($this->mapOptions, $this->getMapOptions(), TRUE);
		$this->width = $this->widgetConfiguration['width'];
		$this->height = $this->widgetConfiguration['height'];
	}

	/**
	 * index action
	 *
	 * @return string
	 */
	public function indexAction() {
		$this->view->assign('center', $this->center);
		$this->view->assign('mapOptions', $this->mapOptions);
		$this->view->assign('width', $this->width);
		$this->view->assign('height', $this->height);
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
			} else $this->widgetConfiguration['mapOptions'][$key] = 1;
		}
		return $this->widgetConfiguration['mapOptions'];
	}

}