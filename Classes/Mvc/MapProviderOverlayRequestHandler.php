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
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct(ObjectManager $objectManager = null)
    {
        $this->objectManager = $objectManager ?? GeneralUtility::makeInstance(ObjectManager::class);
    }

    public function canHandleRequest(): bool
    {
        if (!Environment::isCli()) {
            $mapProviderRequestService = GeneralUtility::makeInstance(MapProviderRequestService::class);
            $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
            $configuration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

            return strtolower($configuration['extensionName']) === 'maps2'
                && !$mapProviderRequestService->isRequestToMapProviderAllowed();
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
        $response = $this->objectManager->get(Response::class);
        $mapService = GeneralUtility::makeInstance(MapService::class);

        $response->appendContent($mapService->showAllowMapForm());
        return $response;
    }
}
