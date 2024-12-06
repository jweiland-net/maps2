<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sometimes there is no need to get PoiCollection via Extbase. Use this service to get records
 * via plain TYPO3 API.
 */
class PoiCollectionService
{
    private const TABLE = 'tx_maps2_domain_model_poicollection';

    public function __construct(
        protected QueryBuilder $queryBuilder
    ) {}

    public function findByUid(int $poiCollectionUid): ?array
    {
        // Will be called by TYPO3 backend, so remove check for hidden, starttime, endtime
        $this->queryBuilder->getRestrictions()->removeAll();
        $this->queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        try {
            $poiCollectionRecord = $this->queryBuilder
                ->select('*')
                ->from(self::TABLE)
                ->where(
                    $this->queryBuilder->expr()->eq(
                        'uid',
                        $this->queryBuilder->createNamedParameter($poiCollectionUid, Connection::PARAM_INT)
                    )
                )
                ->executeQuery()
                ->fetchAssociative();
        } catch (Exception) {
            return null;
        }

        return is_array($poiCollectionRecord) ? $poiCollectionRecord : null;
    }
}
