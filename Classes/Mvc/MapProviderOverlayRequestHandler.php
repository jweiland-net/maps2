<?php
declare(strict_types = 1);
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

use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestHandlerInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * This RequestHandler will be used to show an overlay for maps2 output
 * which will ask users to explicit allow requests to Map Providers.
 * This feature has to be activated in extension manager configuration.
 */
class MapProviderOverlayRequestHandler implements RequestHandlerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager) {
        $this->objectManager = $objectManager;
    }

    /**
     * Checks if the request handler can handle the current request.
     *
     * @return bool true if it can handle the request, otherwise false
     */
    public function canHandleRequest(): bool
    {
        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        if (!$environmentService->isEnvironmentInCliMode()) {
            $mapProviderRequestService = GeneralUtility::makeInstance(MapProviderRequestService::class);
            return $this->buildRequest()->getControllerExtensionKey() === 'maps2'
                && !$mapProviderRequestService->isRequestToMapProviderAllowed();
        }
        return false;
    }

    /**
     * Build a Web Request
     *
     * @return Request
     */
    protected function buildRequest(): Request
    {
        $requestBuilder = $this->objectManager->get(RequestBuilder::class);
        return $requestBuilder->build();
    }

    /**
     * Returns the priority - how eager the handler is to actually handle the
     * request. An integer > 0 means "I want to handle this request" where
     * "100" is default. "0" means "I am a fallback solution".
     *
     * @return int The priority of the request handler
     */
    public function getPriority(): int
    {
        // we must be higher than FrontendRequestHandler (100)
        return 120;
    }

    /**
     * Handles a raw request and returns the response.
     *
     * @return ResponseInterface
     */
    public function handleRequest(): ResponseInterface
    {
        $response = $this->objectManager->get(Response::class);
        $mapService = GeneralUtility::makeInstance(MapService::class);

        $response->appendContent($mapService->showAllowMapForm());
        return $response;
    }
}
