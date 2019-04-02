<?php
namespace JWeiland\Maps2\Tca;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class to register maps2 columns to TCA.
 */
class Maps2Registry implements SingletonInterface
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var FrontendInterface
     */
    protected $maps2RegistryCache;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var array
     */
    protected $addedMaps2Tabs = [];

    /**
     * @var string
     */
    protected $template = '';

    /**
     * Returns a class instance
     *
     * @return Maps2Registry
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(__CLASS__);
    }

    /**
     * Creates this object.
     */
    public function __construct()
    {
        // As this constructor will only be called once after clearing SystemCache
        // we can securely remove all registered fields
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        if ($this->cacheManager->hasCache('maps2_registry') === false) {
            // @link: https://forge.typo3.org/issues/87546
            // Seems we are in ExtensionManager or Installtool.
            // TYPO3 missed to reload CacheConfigurations right after loading ext_localconf.php and before loading TCA.
            $this->cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
            $this->cacheManager->flushCachesInGroup('system');
        }
        $this->maps2RegistryCache = $this->cacheManager->getCache('maps2_registry');

        $this->template = str_repeat(PHP_EOL, 3) . 'CREATE TABLE %s (' . PHP_EOL
            . '  %s int(11) unsigned DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3);
    }

    /**
     * Adds a new maps2 configuration to this registry.
     * TCA changes are directly applied
     *
     * @param string $extensionKey Extension key to be used
     * @param string $tableName Name of the table to be registered
     * @param string $fieldName Name of the field to be registered
     * @param array $options Additional configuration options
     *              + fieldList: field configuration to be added to showitems
     *              + typesList: list of types that shall visualize the tx_maps2_uid field
     *              + position: insert position of the tx_maps2_uid field
     *              + label: backend label of the tx_maps2_uid field
     *              + fieldConfiguration: TCA field config array to override defaults
     *              + addressColumns: Define some columns which should be used to build a full address for google requests
     *              + countryColumn: If you're using static_info_tables in your TCE forms, define the column, which stores the UID of the static country. Else, leave empty
     *              + synchronizeColumns: If you want to sync some columns from foreign record with poi record you can define them here. Multi array. Each array entry needs these keys:
     *                                    -> foreignColumnName: column name of the foreign table. That's your table...of your extension
     *                                    -> poiColumnName: column name of local POI tables. That's ours...from maps2
     * @param bool $override If TRUE, any maps2 configuration for the same table / field is removed before the new configuration is added
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function add($extensionKey, $tableName, array $options = [], $fieldName = 'tx_maps2_uid', $override = false)
    {
        // Do nothing in case of ExtensionManager or Installtool
        if ($this->cacheManager->hasCache('maps2_registry') === false) {
            return false;
        }

        $didRegister = false;
        if (empty($tableName) || !is_string($tableName)) {
            throw new \InvalidArgumentException('No or invalid table name "' . $tableName . '" given.', 1369122038);
        }
        if (empty($extensionKey) || !is_string($extensionKey)) {
            throw new \InvalidArgumentException('No or invalid extension key "' . $extensionKey . '" given.', 1397836158);
        }

        if ($override) {
            $this->remove($tableName, $fieldName);
        }

        $this->registry[$tableName][$fieldName] = $options;
        $this->extensions[$extensionKey][$tableName][$fieldName] = $fieldName;

        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            $this->applyTcaForTableAndField($tableName, $fieldName);
            $this->maps2RegistryCache->set(
                'fields',
                $this->registry
            );
            $didRegister = true;
        }

        return $didRegister;
    }

    /**
     * Gets all extension keys that registered a maps2 configuration.
     *
     * @return array
     */
    public function getExtensionKeys()
    {
        return array_keys($this->extensions);
    }

    /**
     * Gets all tables with a configured maps2 column
     *
     * @return array
     */
    public function getCategorizedTables()
    {
        return array_keys($this->registry);
    }

    /**
     * Tells whether a table has a maps2 configuration in the registry.
     *
     * @param string $tableName Name of the table to be looked up
     * @param string $fieldName Name of the field to be looked up
     * @return bool
     */
    public function isRegistered($tableName, $fieldName = 'tx_maps2_uid')
    {
        return isset($this->registry[$tableName][$fieldName]);
    }

    /**
     * Generates tables definitions for all registered tables.
     *
     * @return string
     */
    public function getDatabaseTableDefinitions()
    {
        $sql = '';
        foreach ($this->getExtensionKeys() as $extensionKey) {
            $sql .= $this->getDatabaseTableDefinition($extensionKey);
        }
        return $sql;
    }

    /**
     * Generates table definitions for registered tables by an extension.
     *
     * @param string $extensionKey Extension key to have the database definitions created for
     * @return string
     */
    public function getDatabaseTableDefinition($extensionKey)
    {
        if (!isset($this->extensions[$extensionKey]) || !is_array($this->extensions[$extensionKey])) {
            return '';
        }
        $sql = '';

        foreach ($this->extensions[$extensionKey] as $tableName => $fields) {
            foreach ($fields as $fieldName) {
                $sql .= sprintf($this->template, $tableName, $fieldName);
            }
        }
        return $sql;
    }

    /**
     * Applies the additions directly to the TCA
     *
     * @param string $tableName
     * @param string $fieldName
     */
    protected function applyTcaForTableAndField($tableName, $fieldName)
    {
        $this->addTcaColumn($tableName, $fieldName, $this->registry[$tableName][$fieldName]);
        $this->addToAllTCAtypes($tableName, $fieldName, $this->registry[$tableName][$fieldName]);
    }

    /**
     * Add a new field into the TCA types -> showitem
     *
     * @param string $tableName Name of the table to save maps2 relation
     * @param string $fieldName Name of the field to be used to store maps2 relations
     * @param array $options Additional configuration options
     *              + fieldList: field configuration to be added to showitems
     *              + typesList: list of types that shall visualize the maps2 field
     *              + position: insert position of the maps2 field
     *
     * @return void
     */
    protected function addToAllTCAtypes($tableName, $fieldName, array $options)
    {

        // Makes sure to add more TCA to an existing structure
        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            if (empty($options['fieldList'])) {
                $fieldList = $this->addMaps2Tab($tableName, $fieldName);
            } else {
                $fieldList = $options['fieldList'];
            }

            $typesList = '';
            if (isset($options['typesList']) && $options['typesList'] !== '') {
                $typesList = $options['typesList'];
            }

            $position = '';
            if (!empty($options['position'])) {
                $position = $options['position'];
            }

            // Makes the new "tx_maps2_uid" field visible in TSFE.
            ExtensionManagementUtility::addToAllTCAtypes($tableName, $fieldList, $typesList, $position);
        }
    }

    /**
     * Creates the 'fieldList' string for $fieldName which includes a maps2 tab.
     * But only one maps2 tab is added per table.
     *
     * @param string $tableName
     * @param string $fieldName
     *
     * @return string
     */
    protected function addMaps2Tab($tableName, $fieldName)
    {
        $fieldList = '';
        if (!isset($this->addedMaps2Tabs[$tableName])) {
            $fieldList .= '--div--;Maps2, ';
            $this->addedMaps2Tabs[$tableName] = $tableName;
        }
        $fieldList .= $fieldName;
        return $fieldList;
    }

    /**
     * Add a new TCA Column
     *
     * @param string $tableName Name of the table to save maps2 relations
     * @param string $fieldName Name of the field to be used to store maps2 relations
     * @param array $options Additional configuration options
     *              + fieldConfiguration: TCA field config array to override defaults
     *              + label: backend label of the maps2 field
     *              + interface: boolean if the maps2 should be included in the "interface" section of the TCA table
     *              + l10n_mode
     *              + l10n_display
     *
     * @return void
     */
    protected function addTcaColumn($tableName, $fieldName, array $options)
    {
        // Makes sure to add more TCA to an existing structure
        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            // Take specific label into account
            $label = 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps2.title';
            if (!empty($options['label'])) {
                $label = $options['label'];
            }

            // Take specific value of exclude flag into account
            $exclude = true;
            if (isset($options['exclude'])) {
                $exclude = (bool)$options['exclude'];
            }

            $fieldConfiguration = empty($options['fieldConfiguration']) ? [] : $options['fieldConfiguration'];

            $columns = [
                $fieldName => [
                    'exclude' => $exclude,
                    'label' => $label,
                    'config' =>  static::getTcaFieldConfiguration($fieldConfiguration),
                ],
            ];

            if (isset($options['l10n_mode'])) {
                $columns[$fieldName]['l10n_mode'] = $options['l10n_mode'];
            }
            if (isset($options['l10n_display'])) {
                $columns[$fieldName]['l10n_display'] = $options['l10n_display'];
            }
            if (isset($options['displayCond'])) {
                $columns[$fieldName]['displayCond'] = $options['displayCond'];
            }

            // Add field to interface list per default (unless the 'interface' property is FALSE)
            if (
                (!isset($options['interface']) || $options['interface'])
                && !empty($GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'])
                && !GeneralUtility::inList($GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'], $fieldName)
            ) {
                $GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'] .= ',' . $fieldName;
            }

            // Adding fields to an existing table definition
            ExtensionManagementUtility::addTCAcolumns($tableName, $columns);
        }
    }

    /**
     * Get the config array.
     * This method does NOT take care of adding sql fields and does NOT add the field to TCA types
     * This has to be taken care of manually!
     *
     * @param array $fieldConfigurationOverride Changes to the default configuration
     *
     * @return array
     */
    public static function getTcaFieldConfiguration(array $fieldConfigurationOverride = [])
    {
        // Forges a new field, default name is "categories"
        $fieldConfiguration = [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'tx_maps2_domain_model_poicollection',
            'prepend_tname' => 0,
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
            'default' => 0,
            'suggestOptions' => [
                'default' => [
                    'searchWholePhrase' => 1,
                ],
            ],
        ];

        // Merge changes to TCA configuration
        if (!empty($fieldConfigurationOverride)) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $fieldConfiguration,
                $fieldConfigurationOverride
            );
        }

        return $fieldConfiguration;
    }

    /**
     * A slot method to inject the required maps2 database field of an
     * extension to the tables definition string
     *
     * Called, when an extension was installed
     *
     * @param array $sqlString
     * @param string $extensionKey
     *
     * @return array
     */
    public function addMaps2DatabaseSchemaToTablesDefinition(array $sqlString, $extensionKey)
    {
        $sqlString[] = $this->getDatabaseTableDefinition($extensionKey);
        return ['sqlString' => $sqlString, 'extensionKey' => $extensionKey];
    }

    /**
     * A slot method to inject the required maps2 database fields of
     * various extensions to the tables definition string
     *
     * Called by Installtool. Compare Database
     *
     * @param array $sqlString
     *
     * @return array
     */
    public function addMaps2DatabaseSchemasToTablesDefinition(array $sqlString)
    {
        $sqlString[] = $this->getDatabaseTableDefinitions();
        return ['sqlString' => $sqlString];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Removes the given field in the given table from the registry if it is found.
     *
     * @param string $tableName The name of the table for which the registration should be removed.
     * @param string $fieldName The name of the field for which the registration should be removed.
     */
    protected function remove($tableName, $fieldName)
    {
        if (!$this->isRegistered($tableName, $fieldName)) {
            return;
        }

        unset($this->registry[$tableName][$fieldName]);

        foreach ($this->extensions as $extensionKey => $tableFieldConfig) {
            foreach ($tableFieldConfig as $extTableName => $fieldNameArray) {
                if ($extTableName === $tableName && isset($fieldNameArray[$fieldName])) {
                    unset($this->extensions[$extensionKey][$tableName][$fieldName]);
                    break;
                }
            }
        }

        // If no more fields are configured we unregister the maps2 tab.
        if (empty($this->registry[$tableName]) && isset($this->addedMaps2Tabs[$tableName])) {
            unset($this->addedMaps2Tabs[$tableName]);
        }
    }
}
