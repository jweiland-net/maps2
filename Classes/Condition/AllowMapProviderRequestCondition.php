<?php
declare(strict_types = 1);
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

use JWeiland\Maps2\Service\MapProviderRequestService;
use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractCondition;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A condition class which checks, if requests to foreign map provider servers are allowed
 */
class AllowMapProviderRequestCondition extends AbstractCondition
{
    /**
     * Check, if extension configuration is set
     * and user has not explicit allowed map provider requests
     *
     * @param array $conditionParameters
     * @return bool
     */
    public function matchCondition(array $conditionParameters): bool
    {
        $mapProviderRequestService = GeneralUtility::makeInstance(MapProviderRequestService::class);
        return $mapProviderRequestService->isRequestToMapProviderAllowed();
    }
}
