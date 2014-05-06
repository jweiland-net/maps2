<?php
namespace JWeiland\Maps2\Dispatch;

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

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxRequest {

	/**
	 * objectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;





	/**
	 * contructor of this class
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

	/**
	 * dispatcher for ajax requests
	 *
	 * @return string html content
	 */
	public function dispatch() {
		// @ToDo generate Pluginnamespace by API-Call
		$parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');

		$className = 'JWeiland\\Maps2\\Ajax\\' . $parameters['objectName'];
		if (class_exists($className)) {
			$object = $this->objectManager->get($className);
			if (method_exists($object, 'processAjaxRequest')) {
				$result = $object->processAjaxRequest($parameters['arguments'], $parameters['hash']);
				return $result;
			}
		}
		return '';
	}

}