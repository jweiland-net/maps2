<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\ExpressionLanguage;

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
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Check, if extension configuration is set
 * and user has not explicit allowed map provider requests
 */
class AllowMapProviderRequestFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            $this->getIsRequestToMapProviderAllowed(),
        ];
    }

    protected function getIsRequestToMapProviderAllowed(): ExpressionFunction
    {
        return new ExpressionFunction(
            'isRequestToMapProviderAllowed',
            function () {
                // Not implemented, we only use the evaluator
            },
            function ($existingVariables) {
                $mapProviderRequestService = GeneralUtility::makeInstance(MapProviderRequestService::class);
                return $mapProviderRequestService->isRequestToMapProviderAllowed();
            });
    }
}
