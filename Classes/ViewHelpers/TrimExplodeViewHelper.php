<?php
namespace JWeiland\Maps2\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Define a delimiter and your comma separated value will be exploded into trimmed parts
 */
class TrimExplodeViewHelper extends AbstractViewHelper
{

    /**
     * implements a vievHelper to trim explode comma separated strings
     *
     * @param string $delimiter The delimiter where the string should be exploded
     * @return array
     */
    public function render($delimiter = ',')
    {
        $value = $this->renderChildren();

        return GeneralUtility::trimExplode($delimiter, $value);
    }
}
