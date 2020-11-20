<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Repository to collect poi collection records
 */
class PoiCollectionRepository extends Repository
{
    /**
     * The TYPO3 page repository. Used for language and workspace overlay
     *
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    protected function getPageRepository(): PageRepository
    {
        if (!$this->pageRepository instanceof PageRepository) {
            if ($this->environmentService->isEnvironmentInFrontendMode() && is_object($GLOBALS['TSFE'])) {
                $this->pageRepository = $GLOBALS['TSFE']->sys_page;
            } else {
                $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
            }
        }
        return $this->pageRepository;
    }

    public function searchWithinRadius(float $latitude, float $longitude, int $radius): QueryResultInterface
    {
        $radiusOfEarth = 6380;

        /** @var Query $query */
        $query = $this->createQuery();
        $sql = '
            SELECT *, ACOS(SIN(RADIANS(?)) * SIN(RADIANS(latitude)) + COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(?) - RADIANS(longitude))) * ? AS distance
            FROM tx_maps2_domain_model_poicollection
            WHERE tx_maps2_domain_model_poicollection.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
            $this->getPageRepository()->enableFields('tx_maps2_domain_model_poicollection') . '
            HAVING distance < ?
            ORDER BY distance;';

        return $query->statement(
            $sql,
            [$latitude, $latitude, $longitude, $radiusOfEarth, $radius]
        )->execute();
    }

    public function findPoisByCategories($categories): QueryResultInterface
    {
        $query = $this->createQuery();
        $orConstraint = [];
        foreach (GeneralUtility::trimExplode(',', $categories) as $category) {
            $orConstraint[] = $query->contains('categories', $category);
        }
        return $query->matching(
            $query->logicalOr($orConstraint)
        )->execute();
    }
}
