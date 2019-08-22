<?php
namespace JWeiland\Maps2\Hook;

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

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * A hook to save the information, if a customer has allowed requests to Servers of Map Providers
 */
class InitFeSessionHook
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * InitFeSessionHook constructor.
     */
    public function __construct()
    {
        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);

        // Start SESSION
        // if not in CLI mode
        // if explicitAllowMapProviderRequests in ExtConf is activated
        // if session was not already started
        if (
            !$environmentService->isEnvironmentInCliMode()
            && $this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
            && session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }
    }

    /**
     * Check GET parameters and allow google requests in session if valid
     *
     * @return void
     */
    public function saveAllowGoogleRequestsInSession()
    {
        $parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');
        if (
            isset($parameters['mapProviderRequestsAllowedForMaps2'])
            && (int)$parameters['mapProviderRequestsAllowedForMaps2'] === 1
            && $this->extConf->getExplicitAllowMapProviderRequests()
        ) {
            if (
                $this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
                && empty($_SESSION['mapProviderRequestsAllowedForMaps2'])
            ) {
                $_SESSION['mapProviderRequestsAllowedForMaps2'] = 1;
            }

            if (
                !$this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
                && (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('mapProviderRequestsAllowedForMaps2') === false
            ) {
                $this->getTypoScriptFrontendController()->fe_user->setAndSaveSessionData('mapProviderRequestsAllowedForMaps2', 1);
            }
        }
    }

    /**
     * @return TypoScriptFrontendController|null
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
