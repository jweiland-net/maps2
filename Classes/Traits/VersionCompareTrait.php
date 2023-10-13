<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Traits;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait to simplify TYPO3 version comparisons
 */
trait VersionCompareTrait
{
    /**
     * This method will call PHP:version_compare where first version is always set with current TYPO3 branch version
     * like 11.5 or 12.4
     *
     * @param string $versionToCompareAgainstTypo3Branch The version to compare against current TYPO3 branch version
     * @param string $operator The operator to be used with PHP:version_compare. "<", ">=", ...
     */
    private function versionCompare(string $versionToCompareAgainstTypo3Branch, string $operator): bool
    {
        return version_compare(
            $this->getTypo3Branch(),
            $versionToCompareAgainstTypo3Branch,
            $operator
        );
    }

    private function getTypo3Branch(): string
    {
        return $this->getTypo3Version()->getBranch();
    }

    private function getTypo3Version(): Typo3Version
    {
        return GeneralUtility::makeInstance(Typo3Version::class);
    }
}
