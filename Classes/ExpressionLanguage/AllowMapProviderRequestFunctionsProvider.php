<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ExpressionLanguage;

use JWeiland\Maps2\Helper\MapHelper;
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
            static function () {},
            static function ($existingVariables) {
                $mapHelper = GeneralUtility::makeInstance(MapHelper::class);
                return $mapHelper->isRequestToMapProviderAllowed();
            }
        );
    }
}
