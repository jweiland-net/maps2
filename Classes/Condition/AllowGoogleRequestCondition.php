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

use JWeiland\Maps2\Service\GoogleRequestService;
use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractCondition;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AllowGoogleRequestCondition
 *
 * @category Condition
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AllowGoogleRequestCondition extends AbstractCondition
{
    /**
     * @var GoogleRequestService
     */
    protected $googleRequestService;

    /**
     * GoogleRequestService constructor.
     *
     * @param GoogleRequestService $googleRequestService
     */
    public function __construct(GoogleRequestService $googleRequestService = null)
    {
        if ($googleRequestService === null) {
            $googleRequestService = GeneralUtility::makeInstance(GoogleRequestService::class);
        }
        $this->googleRequestService = $googleRequestService;
    }

    /**
     * Check, if extension configuration is set
     * and user has not explicit allowed google requests
     *
     * @param array $conditionParameters
     *
     * @return bool
     */
    public function matchCondition(array $conditionParameters)
    {
        return $this->googleRequestService->isGoogleMapRequestAllowed();
    }
}
