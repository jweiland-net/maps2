<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Mvc;

use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\AbstractRequestHandler;

/**
 * This RequestHandler will be used to show an overlay for maps2 output
 * which will ask users to explicit allow requests to Map Providers.
 * This feature has to be activated in extension manager configuration.
 */
class MapProviderOverlayRequestHandler extends AbstractRequestHandler
{
    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var MapService
     */
    protected $mapService;

    public function injectMapProviderRequestService(MapProviderRequestService $mapProviderRequestService): void
    {
        $this->mapProviderRequestService = $mapProviderRequestService;
    }

    public function injectResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function injectMapService(MapService $mapService): void
    {
        $this->mapService = $mapService;
    }

    public function canHandleRequest(): bool
    {
        return $this->environmentService->isEnvironmentInFrontendMode()
            && !Environment::isCli()
            && $this->requestBuilder->build()->getControllerExtensionKey() === 'maps2'
            && !$this->mapProviderRequestService->isRequestToMapProviderAllowed();
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

    public function handleRequest(): ResponseInterface
    {
        $this->response->appendContent(
            $this->mapService->showAllowMapForm()
        );

        return $this->response;
    }
}
