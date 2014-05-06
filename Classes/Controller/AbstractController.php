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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

	/**
	 * @var \JWeiland\Maps2\Utility\DataMapper
	 * @inject
	 */
	protected $dataMapper;





	/**
	 * prepare address for an uri
	 * further it will add some additional informations like country
	 *
	 * @param string $address The address to update
	 * @return string A prepared address which is valid for an uri
	 */
	public function updateAddressForUri($address) {
		// check if it can be interpreted as a zip code
		if (MathUtility::canBeInterpretedAsInteger($address) && strlen($address) == 5) {
			$address .= ' Deutschland';
		}
		return rawurlencode($address);
	}

}