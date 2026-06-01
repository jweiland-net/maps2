<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Configuration\Environment;
use JWeiland\Maps2\Configuration\EnvironmentFactory;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends ActionController
{
    protected Environment $environment;

    public function __construct(
        protected EnvironmentFactory $environmentFactory,
        protected GeoCodeService $geoCodeService,
        protected PoiCollectionRepository $poiCollectionRepository,
    ) {}

    protected function initializeAction(): void
    {
        $this->environment = $this->environmentFactory->buildEnvironment($this->request);
        $this->settings = $this->environment->getSettings();

        if (($this->settings['googleMapsJavaScriptApiKey'] ?? '') === '') {
            $this->addFlashMessage(
                'Google Maps cannot be loaded because no API key is configured for this site. '
                . 'Please check the site settings.',
                'Google Maps cannot be loaded',
                ContextualFeedbackSeverity::WARNING,
            );
        }
    }

    protected function initializeView($view): void
    {
        $view->assign('data', $this->environment->getContentRecord());
        $view->assign('environment', $this->environment);
    }

    /**
     * This action will show the map of Google Maps or OpenStreetMap
     */
    public function showAction(int $poiCollectionUid = 0): ResponseInterface
    {
        $poiCollections = $this->poiCollectionRepository->findPoiCollections(
            $this->settings,
            $poiCollectionUid,
        );

        $fluidVariables = $this->postProcessAndAssignFluidVariables([
            'poiCollections' => $poiCollections,
        ]);

        if ($fluidVariables['poiCollections'] instanceof QueryResultInterface
            && $fluidVariables['poiCollections']->count() === 0
        ) {
            $storagePageIds = $fluidVariables['poiCollections']->getQuery()->getQuerySettings()->getStoragePageIds();
            if ($storagePageIds === [0]) {
                $this->addFlashMessage(
                    'No storage PID has been configured. '
                    . 'Please check the maps2 content element and the site settings.',
                    'Storage PID missing',
                    ContextualFeedbackSeverity::ERROR,
                );
            } else {
                $this->addFlashMessage(
                    'No POI collections were found for the configured storage PID. '
                    . 'Please check whether the correct storage PID is assigned.',
                    'No POI collections found',
                    ContextualFeedbackSeverity::ERROR,
                );
            }
        }

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

    public function searchAction(?Search $search = null): ResponseInterface
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

    protected function postProcessAndAssignFluidVariables(array $variables = []): array
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

        return $event->getFluidVariables();
    }
}
