<?php
namespace JWeiland\Maps2\ViewHelpers\Form;

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
class RenderHiddenFieldsForGetViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Service\ExtensionService
	 * @inject
	 */
	protected $extensionService;

	/**
	 * @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator
	 * @inject
	 */
	protected $cacheHashCalculator;

	/**
	 * implements a vievHelper to trim explode comma separated strings
	 *
	 * @param integer $pageUid UID of target page
	 * @param string $action Target action
	 * @param string $controller Target controller. If NULL current controllerName is used
	 * @return array
	 */
	public function render($pageUid = 0, $action = NULL, $controller = NULL) {
		$pluginNamespace = $this->extensionService->getPluginNamespace(
			$this->controllerContext->getRequest()->getControllerExtensionName(),
			$this->controllerContext->getRequest()->getPluginName()
		);
		// get pageUid
		$pageUid = $pageUid ? $pageUid : $GLOBALS['TSFE']->id;

		// create array for cHash calculation
		$parameters = array();
		$parameters['id'] = $pageUid;
		$parameters[$pluginNamespace]['controller'] = $controller;
		$parameters[$pluginNamespace]['action'] = $action;
		$cachHashArray = $this->cacheHashCalculator->getRelevantParameters(GeneralUtility::implodeArrayForUrl('', $parameters));

		// create array of hidden fields for GET forms
		$fields = array();
		$fields[] = '<input type="hidden" name="id" value="' . $pageUid . '" />';
		$fields[] = '<input type="hidden" name="' . $pluginNamespace . '[controller]" value="' . $controller . '" />';
		$fields[] = '<input type="hidden" name="' . $pluginNamespace . '[action]" value="' . $action . '" />';

		// add cHash
		$fields[] = '<input type="hidden" name="cHash" value="' . $this->cacheHashCalculator->calculateCacheHash($cachHashArray) . '" />';

		return implode(CHR(10), $fields);
	}

}