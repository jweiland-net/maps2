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
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class InitFeSessionHook
 *
 * @category Hook
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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
        if (
            $this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
            && (!isset($_SESSION) || !is_array($_SESSION))
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
            isset($parameters['googleRequestsAllowedForMaps2'])
            && (int)$parameters['googleRequestsAllowedForMaps2'] === 1
            && $this->extConf->getExplicitAllowGoogleMaps()
        ) {
            if (
                $this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
                && empty($_SESSION['googleRequestsAllowedForMaps2'])
            ) {
                $_SESSION['googleRequestsAllowedForMaps2'] = 1;
            }

            if (
                !$this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
                && (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('googleRequestsAllowedForMaps2') === false
            ) {
                $this->getTypoScriptFrontendController()->fe_user->setAndSaveSessionData('googleRequestsAllowedForMaps2', 1);
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
