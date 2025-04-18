<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Controller\Traits\InjectExtConfTrait;
use JWeiland\Maps2\Controller\Traits\InjectGeoCodeServiceTrait;
use JWeiland\Maps2\Controller\Traits\InjectLinkHelperTrait;
use JWeiland\Maps2\Controller\Traits\InjectPoiCollectionRepositoryTrait;
use JWeiland\Maps2\Controller\Traits\InjectSettingsHelperTrait;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Event\PostProcessFluidVariablesEvent;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends ActionController
{
    use InjectExtConfTrait;
    use InjectGeoCodeServiceTrait;
    use InjectLinkHelperTrait;
    use InjectSettingsHelperTrait;
    use InjectPoiCollectionRepositoryTrait;

    public function initializeObject(): void
    {
        $this->settings = $this->settingsHelper->getMergedSettings();
    }

    protected function initializeView($view): void
    {
        $contentRecord = $this->request->getAttribute('currentContentObject')->data;

        // Remove unneeded columns from tt_content array
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource'],
        );

        $view->assign('data', $contentRecord);
        $view->assign('environment', [
            'settings' => $this->settingsHelper->getPreparedSettings($this->settings),
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'ajaxUrl' => $this->linkHelper->buildUriToCurrentPage([], $this->request),
            'contentRecord' => $contentRecord,
        ]);
    }

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
        $poiCollections = new \SplObjectStorage();
        $position = $this->geoCodeService->getFirstFoundPositionByAddress($search->getAddress());
        if ($position instanceof Position) {
            $poiCollections = $this->poiCollectionRepository->searchWithinRadius(
                $position->getLatitude(),
                $position->getLongitude(),
                $search->getRadius(),
            );
        }

        $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $poiCollections,
            'search' => $search,
        ]);

        return $this->htmlResponse();
    }

    protected function postProcessAndAssignFluidVariables(array $variables = []): void
    {
        /** @var PostProcessFluidVariablesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostProcessFluidVariablesEvent(
                $this->request,
                $this->settings,
                $variables,
            ),
        );

        $this->view->assignMultiple($event->getFluidVariables());
    }
}
