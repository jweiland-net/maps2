<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Handle Ajax requests.
 * Currently it is used to render the infoWindowContent of POIs.
 * This controller is not connected to the extbase environment and is reachable
 * over typeNum 1614075471
 */
class AjaxController extends ActionController
{
    public array $errors = [];

    public PoiCollectionRepository $poiCollectionRepository;

    public function injectPoiCollectionRepository(PoiCollectionRepository $poiCollectionRepository): void
    {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    public function processAction(string $method): string
    {
        $response = [
            'content' => ''
        ];

        if ($method === 'renderInfoWindowContent') {
            $response['content'] = $this->renderInfoWindowContentAction(
                (int)$_POST['poiCollection']
            );
        }

        $response['errors'] = $this->errors;

        return \json_encode($response, JSON_THROW_ON_ERROR);
    }

    public function renderInfoWindowContentAction(int $poiCollectionUid): string
    {
        $infoWindowContent = $this->emitRenderInfoWindowSignal($poiCollectionUid);

        if ($infoWindowContent === '') {
            $poiCollection = $this->poiCollectionRepository->findByIdentifier($poiCollectionUid);
            if ($poiCollection instanceof PoiCollection) {
                $mapService = GeneralUtility::makeInstance(MapService::class);
                $infoWindowContent = $mapService->renderInfoWindow($poiCollection);
            } else {
                $this->errors[] = sprintf(
                    'PoiCollection with UID %d could not be found in AjaxController',
                    $poiCollectionUid
                );
            }
        }

        return $infoWindowContent;
    }

    /**
     * With this SignalSlot you can render the info window content on your own way.
     * For performance reasons we do not with PoiCollection object here, that's your work.
     * That way you can decide to use fast array by Doctrine or slow (but feature rich)
     * PoiCollection object.
     */
    protected function emitRenderInfoWindowSignal(int $poiCollectionUid): string
    {
        $infoWindowContent = '';

        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $returnedArguments = $signalSlotDispatcher->dispatch(
            self::class,
            'renderInfoWindow',
            [
                $poiCollectionUid,
                $infoWindowContent,
                $this->configurationManager->getContentObject()
            ]
        );

        return (string)$returnedArguments[1];
    }
}
