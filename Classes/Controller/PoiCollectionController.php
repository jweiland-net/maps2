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
use JWeiland\Maps2\Controller\Traits\InjectLinkHelperTrait;
use JWeiland\Maps2\Controller\Traits\InjectPoiCollectionRepositoryTrait;
use JWeiland\Maps2\Controller\Traits\InjectSettingsHelperTrait;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\Search;
use JWeiland\Maps2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * The main controller to show various kinds of markers on Maps
 */
class PoiCollectionController extends ActionController
{
    use InjectExtConfTrait;
    use InjectLinkHelperTrait;
    use InjectSettingsHelperTrait;
    use InjectPoiCollectionRepositoryTrait;

    public function initializeObject(): void
    {
        $this->settings = $this->settingsHelper->getMergedSettings();
    }

    protected function initializeView($view): void
    {
        $cObj = $this->configurationManager->getContentObject();
        $contentRecord = $cObj->data;

        // Remove unneeded columns from tt_content array
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource']
        );

        $view->assign('data', $contentRecord);
        $view->assign('environment', [
            'settings' => $this->getPreparedSettings(),
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'ajaxUrl' => $this->linkHelper->buildUriToCurrentPage(
                [
                    'type' => '1614075471',
                    'tx_maps2_maps2' => [
                        'controller' => 'Ajax',
                        'action' => 'process',
                        'method' => 'renderInfoWindowContent',
                    ],
                ],
                $this->request
            ),
            'contentRecord' => $contentRecord,
        ]);
    }

    protected function getPreparedSettings(): array
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        if (!array_key_exists('mapProvider', $this->settings)) {
            $this->getFlashMessageQueue()
                ->enqueue(GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'You have forgotten to add maps2 static template for either Google Maps or OpenStreetMap',
                    'Missing static template',
                    ContextualFeedbackSeverity::ERROR
                ));
        }

        return $this->settingsHelper->getPreparedSettings($this->settings);
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

    protected function postProcessAndAssignFluidVariables(array $variables = []): void
    {
        /** @var PostProcessFluidVariablesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostProcessFluidVariablesEvent(
                $this->request,
                $this->settings,
                $variables
            )
        );

        $this->view->assignMultiple($event->getFluidVariables());
    }
}
