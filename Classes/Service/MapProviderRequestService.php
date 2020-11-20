<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * A non extbase orientated service which you can use from nearly everywhere,
 * to check, if a Map should be shown in FE or not.
 */
class MapProviderRequestService
{
    public function isRequestToMapProviderAllowed(): bool
    {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        if ($extConf->getExplicitAllowMapProviderRequests()) {
            if ($extConf->getExplicitAllowMapProviderRequestsBySessionOnly()) {
                return (bool)$_SESSION['mapProviderRequestsAllowedForMaps2'];
            }
            if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
                return (bool)$GLOBALS['TSFE']->fe_user->getSessionData('mapProviderRequestsAllowedForMaps2');
            } else {
                return false;
            }
        }
        return true;
    }
}
