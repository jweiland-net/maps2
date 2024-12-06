<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Define a delimiter and your comma separated value will be exploded into trimmed parts
 */
class TrimExplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'delimiter',
            'string',
            'The delimiter where the string should be exploded',
            false,
            ',',
        );
    }

    /**
     * Implements a ViewHelper to trim explode comma separated strings
     *
     * @return string[]
     */
    public function render(): array
    {
        return GeneralUtility::trimExplode($this->arguments['delimiter'], $this->renderChildren());
    }
}
