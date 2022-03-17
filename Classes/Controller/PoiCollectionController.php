<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends AbstractController
{
    protected PoiCollectionRepository $poiCollectionRepository;

    public function injectPoiCollectionRepository(PoiCollectionRepository $poiCollectionRepository): void
    {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * This action will show the map of Google Maps or OpenStreetMap
     */
    public function showAction(int $poiCollectionUid = 0): void
    {
        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $this->poiCollectionRepository->findPoiCollections($this->settings, $poiCollectionUid)
        ]);
    }

    /**
     * This uncached action will show an overlay which the visitor has to confirm first.
     */
    public function overlayAction(int $poiCollectionUid = 0): void
    {
        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $this->poiCollectionRepository->findPoiCollections($this->settings, $poiCollectionUid),
            'requestUri' => $this->getRequestUri()
        ]);
    }

    public function searchAction(Search $search = null): void
    {
        $search ??= GeneralUtility::makeInstance(Search::class);

        $this->postProcessAndAssignFluidVariables([
            'search' => $search
        ]);
    }

    public function listRadiusAction(Search $search): void
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
            'search' => $search
        ]);
    }

    /**
     * @deprecated
     */
    protected function getRequestUri(): string
    {
        trigger_error('Method PoiCollectionController::getRequestUri is deprecated. Use RequestUriForOverlayViewHelper instead.', E_USER_DEPRECATED);

        // Method setAddQueryStringMethod is deprecated with TYPO3 11. Remove while removing TYPO3 10 compatibility
        $uriBuilder = $this->uriBuilder
            ->reset()
            ->setAddQueryString(true)
            ->setAddQueryStringMethod('GET')
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1
                ]
            ])
            ->setArgumentsToBeExcludedFromQueryString(['cHash']);

        if (($this->settings['overlay']['link']['addSection'] ?? '') === '1') {
            $contentObject = $this->configurationManager->getContentObject();
            if (
                $contentObject instanceof ContentObjectRenderer
                && isset($contentObject->data['uid'])
                && $contentObject->data['uid']
            ) {
                $uriBuilder->setSection('c' . $contentObject->data['uid']);
            }
        }

        return $uriBuilder->build();
    }
}
