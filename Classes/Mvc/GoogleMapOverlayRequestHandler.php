<?php
namespace JWeiland\Maps2\Mvc;

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

use JWeiland\Maps2\Service\GoogleRequestService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Extbase\Mvc\RequestHandlerInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\AbstractRequestHandler;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class GoogleMapOverlayRequestHandler
 *
 * This RequestHandler will be used to show an overlay for maps2 output
 * which will ask users to explicit allow google map requests.
 * This feature has to be activated in extension manager configuration.
 */
class GoogleMapOverlayRequestHandler extends AbstractRequestHandler
{
    /**
     * @var MapService
     */
    protected $mapService;

    /**
     * @var GoogleRequestService
     */
    protected $googleRequestService;

    /**
     * inject mapService
     *
     * @param MapService $mapService
     *
     * @return void
     */
    public function injectMapService(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    /**
     * inject googleRequestService
     *
     * @param GoogleRequestService $googleRequestService
     *
     * @return void
     */
    public function injectGoogleRequestService(GoogleRequestService $googleRequestService)
    {
        $this->googleRequestService = $googleRequestService;
    }

    /**
     * Checks if the request handler can handle the current request.
     *
     * @return bool true if it can handle the request, otherwise false
     */
    public function canHandleRequest() {
        if (!$this->environmentService->isEnvironmentInCliMode()) {
            $request = $this->requestBuilder->build();
            return $request->getControllerExtensionKey() === 'maps2'
                && !$this->googleRequestService->isGoogleMapRequestAllowed();
        }
        return false;
    }

    /**
     * Returns the priority - how eager the handler is to actually handle the
     * request. An integer > 0 means "I want to handle this request" where
     * "100" is default. "0" means "I am a fallback solution".
     *
     * @return int The priority of the request handler
     */
    public function getPriority() {
        // we must be higher than FrontendRequestHandler (100)
        return 120;
    }
    /**
     * Handles a raw request and returns the respsonse.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    public function handleRequest() {
        /** @var ResponseInterface $response */
        $response = $this->objectManager->get(Response::class);
        $response->appendContent($this->mapService->showAllowMapForm());
        return $response;
    }
}
