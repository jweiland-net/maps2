<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Ajax;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

/**
 * An abstract class for ajax requests
 */
abstract class AbstractAjaxRequest implements AjaxInterface
{
    /**
     * @var HashService
     */
    protected $hashService;

    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * Find PoiCollection record by UID
     *
     * @param int $poiCollectionUid
     * @return array
     */
    protected function getPoiCollection(int $poiCollectionUid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $poiCollection = $queryBuilder
            ->select('*')
            ->from('tx_maps2_domain_model_poicollection')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($poiCollectionUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if ($poiCollection === false) {
            $poiCollection = [];
        }

        return $poiCollection;
    }

    /**
     * Validate arguments against hash
     *
     * @param array $poiCollection The POI collection record
     * @param string $hash A generated hash value to verify that there are no modifications in the uri
     * @return bool
     */
    public function validateArguments(array $poiCollection, string $hash): bool
    {
        $isValidPoiCollection = false;
        if (!empty($poiCollection)) {
            $isValidPoiCollection = $this->hashService->validateHmac(
                serialize([
                    'uid' => $poiCollection['uid'],
                    'collectionType' => $poiCollection['collection_type']
                ]),
                $hash
            );
        }
        return $isValidPoiCollection;
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
