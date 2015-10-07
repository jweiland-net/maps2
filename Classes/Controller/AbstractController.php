<?php
namespace JWeiland\Maps2\Controller;

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
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractController extends ActionController {

	/**
	 * @var \JWeiland\Maps2\Configuration\ExtConf
	 */
	protected $extConf;

	/**
	 * @var \JWeiland\Maps2\Utility\DataMapper
	 */
	protected $dataMapper;

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
	 * inject dataMapper
	 *
	 * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
	 * @return void
	 */
	public function injectDataMapper(\JWeiland\Maps2\Utility\DataMapper $dataMapper) {
		$this->dataMapper = $dataMapper;
	}

	/**
	 * prepare address for an uri
	 * further it will add some additional informations like country
	 *
	 * @param string $address The address to update
	 * @return string A prepared address which is valid for an uri
	 */
	public function updateAddressForUri($address) {
		// check if it can be interpreted as a zip code
		if (MathUtility::canBeInterpretedAsInteger($address) && strlen($address) === 5) {
			$address .= ' Deutschland';
		}
		return rawurlencode($address);
	}

}