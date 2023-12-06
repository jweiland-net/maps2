<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Traits;

use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;

/**
 * Trait to provide full web-path of given FileReference
 */
trait GetWebPathOfFileReferenceTrait
{
    /**
     * ToDo: Add both types to method while removing TYPO3 11 compatibility
     *
     * @param CoreFileReference|ExtbaseFileReference|null $fileReference
     */
    private function getWebPathOfFileReference($fileReference): string
    {
        $coreFileReference = $fileReference;
        if ($fileReference instanceof ExtbaseFileReference) {
            $coreFileReference = $fileReference->getOriginalResource();
        }

        if ($coreFileReference instanceof CoreFileReference) {
            return $this->getTypo3SiteUrl() . $coreFileReference->getPublicUrl();
        }

        return '';
    }

    /**
     * Returns the TYPO3_SITE_URL without ending Slash.
     */
    private function getTypo3SiteUrl(): string
    {
        return rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/');
    }
}
