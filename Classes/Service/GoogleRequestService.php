<?php
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
 * Class GoogleRequestService
 *
 * @category Utility
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class GoogleRequestService
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * GoogleRequestService constructor.
     *
     * @param ExtConf $extConf
     */
    public function __construct(ExtConf $extConf = null)
    {
        if ($extConf === null) {
            $extConf = GeneralUtility::makeInstance('JWeiland\\Maps2\\Configuration\\ExtConf');
        }
        $this->extConf = $extConf;
    }

    /**
     * Check, if Browser(Cookie) or $_SESSION allows request to Google Maps Servers
     *
     * @return bool
     */
    public function isGoogleMapRequestAllowed()
    {
        if ($this->extConf->getExplicitAllowGoogleMaps()) {
            if ($this->extConf->getExplicitAllowGoogleMapsBySessionOnly()) {
                return (bool)$_SESSION['googleRequestsAllowedForMaps2'];
            } else {
                if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
                    return (bool)$GLOBALS['TSFE']->fe_user->getSessionData('googleRequestsAllowedForMaps2');
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }
}
