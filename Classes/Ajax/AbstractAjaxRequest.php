<?php
namespace JWeiland\Maps2\Ajax;

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
abstract class AbstractAjaxRequest implements \JWeiland\Maps2\Ajax\AjaxInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager
	 * @inject
	 */
	protected $backendConfigurationManager;


	/**
	 * In Typo3QuerySettings is a feature check which loads whole TS which needs about 250ms
	 * With this workaround I modify the 1st level cache of configuration manager
	 *
	 * @return void
	 */
	public function initializeObject() {
		// set minimal configuration
		$configuration = array();
		$configuration['_']['features']['ignoreAllEnableFieldsInBe'] = 0;

		// transport our minimal configuration into backendConfigurationManagers 1st-level Cache
		if (property_exists(get_class($this->backendConfigurationManager), 'configurationCache')) {
			$propertyReflection = new \TYPO3\CMS\Extbase\Reflection\PropertyReflection(get_class($this->backendConfigurationManager), 'configurationCache');
			$propertyReflection->setAccessible(TRUE);
			$propertyReflection->setValue($this->backendConfigurationManager, $configuration);
		}
	}
	/**
	 * validate arguments against hash
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection Model to validate hash against
	 * @param string $hash A generated hash value to verify that there are no modifications in the uri
	 * @return boolean
	 */
	public function validateArguments(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection, $hash) {
		$hashArray['uid'] = $poiCollection->getUid();
		$hashArray['collectionType'] = $poiCollection->getCollectionType();
		return $this->hashService->validateHmac(serialize($hashArray), $hash);
	}

}