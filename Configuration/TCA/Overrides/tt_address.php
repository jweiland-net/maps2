<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
            'tt_address',
            'tt_address',
            [
                'addressColumns' => ['address', 'zip', 'city'],
                'countryColumn' => 'country',
                'synchronizeColumns' => [
                    [
                        'foreignColumnName' => 'name',
                        'poiCollectionColumnName' => 'title'
                    ]
                ]
            ]
        );
    }
});
