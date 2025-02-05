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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sometimes there is no need to get PoiCollection via Extbase. Use this service to get records
 * via plain TYPO3 API.
 */
class PoiCollectionService
{
    private const TABLE = 'tx_maps2_domain_model_poicollection';

    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly PageRepository $pageRepository,
    ) {}

    public function findByUid(int $poiCollectionUid, ServerRequestInterface $request): ?array
    {
        $queryBuilder = $this->getQueryBuilder($request);

        try {
            $poiCollectionRecord = $queryBuilder
                ->select('*')
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($poiCollectionUid, Connection::PARAM_INT)
                    )
                )
                ->executeQuery()
                ->fetchAssociative();
        } catch (Exception) {
            return null;
        }

        if (is_array($poiCollectionRecord)) {
            $this->pageRepository->versionOL(self::TABLE, $poiCollectionRecord);
            if (is_array($poiCollectionRecord)) {
                $poiCollectionRecord = $this->pageRepository->getLanguageOverlay(
                    self::TABLE,
                    $poiCollectionRecord,
                );
            }
        }

        return is_array($poiCollectionRecord) ? $poiCollectionRecord : null;
    }

    private function getQueryBuilder(ServerRequestInterface $request): QueryBuilder
    {
        $queryBuilder = $this->queryBuilder;

        if (ApplicationType::fromRequest($request)->isFrontend()) {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        } else {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        }

        return $queryBuilder;
    }
}
