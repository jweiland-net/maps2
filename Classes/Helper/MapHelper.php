<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Little helper with a very reduced set of dependencies. Useful, if you need f.e. MapProvider at
 * a very early state of TYPO3 like Middlewares. As it does not load Extbase, it should not be a problem.
 */
class MapHelper
{
    /**
     * Get currently valid default map provider
     *
     * @param array $databaseRow If set, we will try to retrieve map provider from this row before.
     * @return string Returns either "gm" or "osm"
     */
    public function getMapProvider(array $databaseRow = []): string
    {
        $mapProvider = '';
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        // Only if both map providers are allowed, we can read map provider from Database
        if ($extConf->getMapProvider() === 'both') {
            if (!empty($databaseRow)) {
                $mapProvider = $this->getMapProviderFromDatabase($databaseRow);
            }

            if (empty($mapProvider)) {
                $mapProvider = $extConf->getDefaultMapProvider();
            }
        } else {
            // We have a strict map provider.
            $mapProvider = $extConf->getMapProvider();
        }

        return $mapProvider;
    }

    /**
     * Try to retrieve a default map provider from given database record
     *
     * @param array $databaseRow
     * @return string
     */
    protected function getMapProviderFromDatabase(array $databaseRow): string
    {
        $mapProvider = '';

        if (
            array_key_exists('map_provider', $databaseRow)
            && !empty($databaseRow['map_provider'])
        ) {
            if (is_array($databaseRow['map_provider'])) {
                // We have a record from TCEMAIN
                $mapProvider = (string)current($databaseRow['map_provider']);
            } elseif (is_string($databaseRow['map_provider'])) {
                // We have a normal array based record from database
                $mapProvider = $databaseRow['map_provider'];
            }
        }

        return $mapProvider;
    }
}
