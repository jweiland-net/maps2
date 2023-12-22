<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to register maps2 columns to TCA.
 */
class Maps2Registry implements SingletonInterface
{
    protected array $registry = [];

    protected array $extensions = [];

    protected array $addedMaps2Tabs = [];

    protected string $configurationFile = '';

    protected string $template = '';

    public static function getInstance(): self
    {
        return GeneralUtility::makeInstance(self::class);
    }

    public function __construct()
    {
        $this->configurationFile = Environment::getConfigPath() . '/Maps2/Registry.json';
        $this->template = str_repeat(PHP_EOL, 3) . 'CREATE TABLE %s (' . PHP_EOL
            . '  %s int(11) unsigned DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3);
    }

    protected function initialize(): void
    {
        if (@is_file($this->configurationFile)) {
            try {
                $configuration = json_decode(
                    file_get_contents($this->configurationFile),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (
                    is_array($configuration)
                    && array_key_exists('registry', $configuration)
                    && array_key_exists('extensions', $configuration)
                ) {
                    $this->registry = $configuration['registry'];
                    $this->extensions = $configuration['extensions'];
                }
            } catch (\JsonException $jsonException) {
                // File exists, but empty or other error
                $this->registry = [];
                $this->extensions = [];
            }
        } else {
            GeneralUtility::mkdir_deep(dirname($this->configurationFile));
            GeneralUtility::writeFile($this->configurationFile, '{}');
        }
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
     *              + addressColumns: Define some columns which should be used to build a full address for Google requests
     *              + countryColumn: If you're using static_info_tables in your TCE forms, define the column, which stores the UID of the static country. Else, leave empty
     *              + synchronizeColumns: If you want to sync some columns from foreign record with poi record you can define them here. Multi array. Each array entry needs these keys:
     *                                    -> foreignColumnName: column name of the foreign table. That's your table...of your extension
     *                                    -> poiColumnName: column name of local POI tables. That's ours...from maps2
     * @param bool $override If TRUE, any maps2 configuration for the same table / field is removed before the new configuration is added
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function add(
        string $extensionKey,
        string $tableName,
        array $options = [],
        string $fieldName = 'tx_maps2_uid',
        bool $override = false
    ): bool {
        $this->initialize();
        $didRegister = false;
        if ($tableName === '') {
            throw new \InvalidArgumentException('No or invalid table name "' . $tableName . '" given.', 1369122038);
        }

        if ($extensionKey === '') {
            throw new \InvalidArgumentException('No or invalid extension key "' . $extensionKey . '" given.', 1397836158);
        }

        if ($override) {
            $this->remove($tableName, $fieldName);
        }

        $this->registry[$tableName][$fieldName] = $options;
        $this->extensions[$extensionKey][$tableName][$fieldName] = $fieldName;

        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            $this->applyTcaForTableAndField($tableName, $fieldName);
            file_put_contents(
                $this->configurationFile,
                json_encode([
                    'registry' => $this->registry,
                    'extensions' => $this->extensions,
                ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            );
            $didRegister = true;
        }

        return $didRegister;
    }

    /**
     * Reads, extract and returns Maps2 Column Configuration (Registry)
     *
     * @api
     * @return array
     */
    public function getColumnRegistry(): array
    {
        $columnRegistry = [];
        if (@is_file($this->configurationFile)) {
            try {
                $configuration = json_decode(
                    file_get_contents($this->configurationFile),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (\JsonException $jsonException) {
                $configuration = [];
            }

            if (
                is_array($configuration)
                && array_key_exists('registry', $configuration)
                && is_array($configuration['registry'])
            ) {
                $columnRegistry = $configuration['registry'];
            }
        }

        return $columnRegistry;
    }

    /**
     * @return int[]|string[]
     */
    public function getExtensionKeys(): array
    {
        return array_keys($this->extensions);
    }

    /**
     * @return int[]|string[]
     */
    public function getCategorizedTables(): array
    {
        return array_keys($this->registry);
    }

    public function isRegistered(string $tableName, string $fieldName = 'tx_maps2_uid'): bool
    {
        return isset($this->registry[$tableName][$fieldName]);
    }

    public function getDatabaseTableDefinitions(): string
    {
        $sql = '';
        foreach ($this->getExtensionKeys() as $extensionKey) {
            $sql .= $this->getDatabaseTableDefinition($extensionKey);
        }

        return $sql;
    }

    public function getDatabaseTableDefinition(string $extensionKey): string
    {
        if (!isset($this->extensions[$extensionKey])) {
            return '';
        }

        if (!is_array($this->extensions[$extensionKey])) {
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

    protected function applyTcaForTableAndField(string $tableName, string $fieldName): void
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
     */
    protected function addToAllTCAtypes(string $tableName, string $fieldName, array $options): void
    {
        // Makes sure to add more TCA to an existing structure
        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            $fieldList = empty($options['fieldList']) ? $this->addMaps2Tab($tableName, $fieldName) : $options['fieldList'];

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

    protected function addMaps2Tab(string $tableName, string $fieldName): string
    {
        $fieldList = '';
        if (!isset($this->addedMaps2Tabs[$tableName])) {
            $fieldList .= '--div--;Maps2, ';
            $this->addedMaps2Tabs[$tableName] = $tableName;
        }

        return $fieldList . $fieldName;
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
     */
    protected function addTcaColumn(string $tableName, string $fieldName, array $options): void
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
     */
    public static function getTcaFieldConfiguration(array $fieldConfigurationOverride = []): array
    {
        // Forges a new field, default name is "categories"
        $fieldConfiguration = [
            'type' => 'group',
            'allowed' => 'tx_maps2_domain_model_poicollection',
            'foreign_table' => 'tx_maps2_domain_model_poicollection',
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
     */
    public function addMaps2DatabaseSchemaToTablesDefinition(array $sqlString, string $extensionKey): array
    {
        $sqlString[] = $this->getDatabaseTableDefinition($extensionKey);
        return ['sqlString' => $sqlString, 'extensionKey' => $extensionKey];
    }

    /**
     * A slot method to inject the required maps2 database fields of
     * various extensions to the table definition string
     */
    public function addMaps2DatabaseSchemasToTablesDefinition(
        AlterTableDefinitionStatementsEvent $alterTableDefinitionStatementsEvent
    ): void {
        $this->initialize();
        $alterTableDefinitionStatementsEvent->addSqlData($this->getDatabaseTableDefinitions());
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Removes the given field in the given table from the registry if it is found.
     *
     * @param string $tableName The name of the table for which the registration should be removed.
     * @param string $fieldName The name of the field for which the registration should be removed.
     */
    protected function remove(string $tableName, string $fieldName): void
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
        if (!empty($this->registry[$tableName])) {
            return;
        }

        if (!isset($this->addedMaps2Tabs[$tableName])) {
            return;
        }

        unset($this->addedMaps2Tabs[$tableName]);
    }
}
