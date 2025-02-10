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
    private function getWebPathOfFileReference(CoreFileReference|ExtbaseFileReference|null $fileReference): string
    {
        $coreFileReference = $fileReference;
        if ($fileReference instanceof ExtbaseFileReference) {
            $coreFileReference = $fileReference->getOriginalResource();
        }

        if ($coreFileReference instanceof CoreFileReference && $coreFileReference->getPublicUrl() !== null) {
            return GeneralUtility::locationHeaderUrl($coreFileReference->getPublicUrl());
        }

        return '';
    }
}
