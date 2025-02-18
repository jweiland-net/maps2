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

/**
 * Check, if extension configuration is set
 * and user has not explicit allowed map provider requests
 */
class AllowMapProviderRequestFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(protected MapHelper $mapHelper) {}

    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            $this->getIsRequestToMapProviderAllowed(),
        ];
    }

    protected function getIsRequestToMapProviderAllowed(): ExpressionFunction
    {
        // Need local variable for static callable
        $mapHelper = $this->mapHelper;

        return new ExpressionFunction(
            'isRequestToMapProviderAllowed',
            static function (): void {
                // Not implemented, we only use the evaluator
            },
            static fn($existingVariables) => $mapHelper->isRequestToMapProviderAllowed(),
        );
    }
}
