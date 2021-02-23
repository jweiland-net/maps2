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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\RequestHandlerInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var MapService
     */
    protected $mapService;

    public function __construct(
        MapProviderRequestService $mapProviderRequestService = null,
        ConfigurationManagerInterface $configurationManager = null,
        Response $response = null,
        MapService $mapService = null
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->mapProviderRequestService = $mapProviderRequestService ?? GeneralUtility::makeInstance(MapProviderRequestService::class);
        $this->configurationManager = $configurationManager ?? $objectManager->get(ConfigurationManagerInterface::class);
        $this->response = $response ?? $objectManager->get(Response::class);
        $this->mapService = $mapService ?? GeneralUtility::makeInstance(MapService::class);
    }

    public function canHandleRequest(): bool
    {
        if (!Environment::isCli()) {
            $configuration = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );

            return strtolower($configuration['extensionName']) === 'maps2'
                && !$this->mapProviderRequestService->isRequestToMapProviderAllowed();
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
