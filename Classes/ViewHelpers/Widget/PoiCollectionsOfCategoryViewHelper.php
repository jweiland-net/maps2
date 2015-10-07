<?php
namespace JWeiland\Maps2\ViewHelpers\Widget;

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
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PoiCollectionsOfCategoryViewHelper extends AbstractWidgetViewHelper {

	/**
	 * @var \JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController
	 */
	protected $controller;

	/**
	 * inject controller
	 *
	 * @param \JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController $controller
	 * @return void
	 */
	public function injectController(\JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController $controller) {
		$this->controller = $controller;
	}

	/**
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $poiCollections
	 * @return string
	 */
	public function render(\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $poiCollections) {
		return $this->initiateSubRequest();
	}

}