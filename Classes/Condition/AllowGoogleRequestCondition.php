<?php
namespace JWeiland\Maps2\Condition;

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
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractCondition;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AllowGoogleRequestCondition
 *
 * @category Condition
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AllowGoogleRequestCondition extends AbstractCondition
{
    /**
     * Check, if extension configuration is set
     * and user has not explicit allowed google requests
     *
     * @param array $conditionParameters
     *
     * @return bool
     */
    public function matchCondition(array $conditionParameters) {
        /** @var ExtConf $extConf */
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        if ($extConf->getExplicitAllowGoogleMaps()) {
            if ($extConf->getExplicitAllowGoogleMapsBySessionOnly()) {
                return (bool)$_SESSION['googleRequestsAllowedForMaps2'];
            } else {
                if ($this->getTypoScriptFrontendController() instanceof TypoScriptFrontendController) {
                    return (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('googleRequestsAllowedForMaps2');
                } else {
                    return false;
                }
            }
        } else {
            return true;
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

