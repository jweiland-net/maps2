<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Controller\Traits\InjectPoiCollectionRepositoryTrait;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends AbstractController
{
    use InjectPoiCollectionRepositoryTrait;

    /**
     * This action will show the map of Google Maps or OpenStreetMap
     */
    public function showAction(int $poiCollectionUid = 0): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $this->poiCollectionRepository->findPoiCollections($this->settings, $poiCollectionUid),
        ]);

        return $this->htmlResponse();
    }

    /**
     * This uncached action will show an overlay which the visitor has to confirm first.
     */
    public function overlayAction(int $poiCollectionUid = 0): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $this->poiCollectionRepository->findPoiCollections($this->settings, $poiCollectionUid),
        ]);

        return $this->htmlResponse();
    }

    public function searchAction(Search $search = null): ResponseInterface
    {
        $search ??= GeneralUtility::makeInstance(Search::class);

        $this->postProcessAndAssignFluidVariables([
            'search' => $search,
        ]);

        return $this->htmlResponse();
    }

    public function listRadiusAction(Search $search): ResponseInterface
    {
        $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);

        $poiCollections = new \SplObjectStorage();
        $position = $geoCodeService->getFirstFoundPositionByAddress($search->getAddress());
        if ($position instanceof Position) {
            $poiCollections = $this->poiCollectionRepository->searchWithinRadius(
                $position->getLatitude(),
                $position->getLongitude(),
                $search->getRadius()
            );
        }

        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $poiCollections,
            'search' => $search,
        ]);

        return $this->htmlResponse();
    }
}
