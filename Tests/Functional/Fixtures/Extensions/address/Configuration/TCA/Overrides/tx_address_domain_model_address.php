<?php

/*
 * This file is part of the package jweiland/address.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Maps2\Tca\Maps2Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (ExtensionManagementUtility::isLoaded('maps2')) {
    Maps2Registry::getInstance()->add(
        'address',
        'tx_address_domain_model_address',
        [
            'addressColumns' => ['address', 'house_number', 'zip', 'city'],
            'countryColumn' => 'country',
            'synchronizeColumns' => [
                [
                    'foreignColumnName' => 'title',
                    'poiCollectionColumnName' => 'title'
                ]
            ]
        ]
    );
}
