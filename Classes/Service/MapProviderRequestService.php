<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Service;

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
            } else {
                if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
                    return (bool)$GLOBALS['TSFE']->fe_user->getSessionData('mapProviderRequestsAllowedForMaps2');
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }
}
