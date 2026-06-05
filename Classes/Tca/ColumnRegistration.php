<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

final readonly class ColumnRegistration
{
    /**
     * The table name. It must be registered in TCA of TYPO3
     */
    private string $tableName;

    /**
     * The column name of the table above. It must be registered in TCA of TYPO3
     */
    private string $columnName;

    /**
     * Choose the country column from the address columns above that should be used
     * as the country column
     */
    private string $countryColumn;

    /**
     * The default country
     *
     * This country will be used if no country is provided by one of the address columns
     * Value can be IS0-2, ISO-3, English name, or any other country name
     */
    private string $defaultCountry;

    public function __construct(
        string $tableName,
        string $columnName,
        /**
         * Choose the address columns from the table declared above.
         * All columns have to be registered in TCA of TYPO3.
         * We prefer to order the columns like used in the address form
         * for the best matches with geocoding APIs.
         * F.e. Street, house number, zip, city, country
         */
        private array $addressColumns,
        string $countryColumn = '',
        string $defaultCountry = '',
        /**
         * Column Match
         */
        private array $columnMatch = [],
        /**
         * The default storage PID
         *
         * This PID will be used to store the map records if no PID is provided
         * by the foreign location record.
         *
         * If integer, the value will be used as it is
         * Use array of StoragePidConfiguration objects to add more complex configurations
         *
         * @var int|StoragePidLocation[]
         */
        private int|array $defaultStoragePid = 0,
        /**
         * Columns to synchronize from foreign table to poi collection table
         * of maps2.
         *
         * @var SynchronizeColumn[]
         */
        private array $synchronizeColumns = [],
        /**
         * If another extension already adds a column configuration, you have the possibility
         * to override their column configuration with your own one.
         */
        private bool $override = false,
    ) {
        $this->tableName = strtolower(trim($tableName));
        $this->columnName = strtolower(trim($columnName));
        $this->countryColumn = trim($countryColumn);
        $this->defaultCountry = trim($defaultCountry);
    }

    public function getTableName(): string
    {
        return strtolower(trim($this->tableName));
    }

    public function getColumnName(): string
    {
        return strtolower(trim($this->columnName));
    }

    public function getAddressColumns(): array
    {
        $addressColumns = $this->addressColumns;

        // remove countryColumn from addressColumns
        if (($this->countryColumn !== '' && $this->countryColumn !== '0')) {
            $key = array_search($this->countryColumn, $addressColumns);
            if ($key) {
                unset($addressColumns[$key]);
            }
        }

        return $addressColumns;
    }

    public function getCountryColumn(): string
    {
        return $this->countryColumn;
    }

    public function getDefaultCountry(): string
    {
        return $this->defaultCountry;
    }

    /**
     * @return ColumnMatch[]
     */
    public function getColumnMatch(): array
    {
        return $this->columnMatch;
    }

    /**
     * @return int|StoragePidLocation[]
     */
    public function getDefaultStoragePid(): int|array
    {
        return $this->defaultStoragePid;
    }

    public function hasDefaultStoragePid(): bool
    {
        if (is_array($this->defaultStoragePid)) {
            return $this->defaultStoragePid !== [];
        }

        return $this->defaultStoragePid !== 0;
    }

    /**
     * @return SynchronizeColumn[]
     */
    public function getSynchronizeColumns(): array
    {
        return $this->synchronizeColumns;
    }

    public function isOverride(): bool
    {
        return $this->override;
    }
}
