<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Traits;

use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait to provide an instance of MapHelper
 */
trait GetMapHelperTrait
{
    private function getMapHelper(): MapHelper
    {
        static $mapHelper;

        if ($mapHelper === null) {
            $mapHelper = GeneralUtility::makeInstance(MapHelper::class);
        }

        return $mapHelper;
    }
}
