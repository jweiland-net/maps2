<?php
namespace JWeiland\Maps2\ViewHelpers;

/**
 * This file is part of the TYPO3 CMS project.
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
 * Class ConvertToJsonViewHelper
 *
 * @category ViewHelpers
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class ConvertToJsonViewHelper extends AbstractViewHelper
{

    /**
     * implements a ViewHelper to convert an array into JSON format
     *
     * @return array
     */
    public function render()
    {
        $value = $this->renderChildren();
        if (is_array($value)) {
            return htmlspecialchars(json_encode($value));
        } else {
            return '{}';
        }
    }
}
