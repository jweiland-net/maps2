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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestHandlerInterface;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * This RequestHandler will be used to show an overlay for maps2 output
 * which will ask users to explicit allow requests to Map Providers.
 * This feature has to be activated in extension manager configuration.
 */
class MapProviderOverlayRequestHandler implements RequestHandlerInterface
{
    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var MapService
     */
    protected $mapService;

    /**
     * @var RequestBuilder
     */
    protected $requestBuilder;

    public function __construct(
        MapProviderRequestService $mapProviderRequestService,
        MapService $mapService,
        RequestBuilder $requestBuilder
    ) {
        $this->mapProviderRequestService = $mapProviderRequestService;
        $this->mapService = $mapService;
        $this->requestBuilder = $requestBuilder;
    }

    public function canHandleRequest(): bool
    {
        return $this->isEnvironmentInFrontendMode()
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
        $response = GeneralUtility::makeInstance(Response::class);
        $response->appendContent(
            $this->mapService->showAllowMapForm()
        );

        return $response;
    }

    /**
     * EnvironmentService is deprecated in TYPO3 11. So this is a copy
     * of the new solution.
     *
     * @return bool
     */
    protected function isEnvironmentInFrontendMode(): bool
    {
        // Frontend mode stays false if backend or cli without request object
        return ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }
}
