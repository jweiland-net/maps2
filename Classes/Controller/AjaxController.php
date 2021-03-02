<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Controller;

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
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var PoiCollectionRepository
     */
    public $poiCollectionRepository;

    public function __construct(PoiCollectionRepository $poiCollectionRepository)
    {
        parent::__construct();
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * @param string $method
     * @return string
     */
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

        return \json_encode($response);
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
     *
     * @param int $poiCollectionUid
     * @return string
     */
    protected function emitRenderInfoWindowSignal(int $poiCollectionUid): string {
        $infoWindowContent = '';

        $signalSlotDispatcher = $this->objectManager->get(Dispatcher::class);
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
