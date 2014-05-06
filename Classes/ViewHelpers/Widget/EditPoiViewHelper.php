<?php
namespace JWeiland\Maps2\ViewHelpers\Widget;

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

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EditPoiViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @var \JWeiland\Maps2\ViewHelpers\Widget\Controller\EditPoiController
	 * @inject
	 */
	protected $controller;

	/**
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection
	 * @param integer $width Width of the map
	 * @param integer $height Height of the map
	 * @param string $prepend Extension/Plugin combination to identify GET/POST vars: tx_yellowpages2_directory
	 * @param string $id This is the id-Attribute of the div, where to show the map. Defaults to "maps2" if not given. This is good to display multiple maps on one page.
	 * @param array $mapOptions Google Map Options
	 * @return string
	 */
	public function render(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection, $width = 400, $height = 300, $prepend = '', $id = 'maps2', array $mapOptions = array()) {
		return $this->initiateSubRequest();
	}

}