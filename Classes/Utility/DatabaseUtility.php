<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Utility;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A little helper to organize our DB queries
 */
class DatabaseUtility
{
    /**
     * maps2 internal we only need the array keys to filter out invalid columns.
     *
     * @param string $tableName
     * @return array|Column[]
     */
    public static function getColumnsFromTable(string $tableName): array
    {
        $columns = [];
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        if ($connection->getSchemaManager() instanceof AbstractSchemaManager) {
            $columns = $connection->getSchemaManager()->listTableColumns($tableName);
        }

        return $columns;
    }
}
