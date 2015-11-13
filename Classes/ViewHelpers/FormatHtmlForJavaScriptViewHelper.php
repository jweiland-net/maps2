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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FormatHtmlForJavaScriptViewHelper
 *
 * @category ViewHelpers
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class FormatHtmlForJavaScriptViewHelper extends AbstractViewHelper
{

    /**
     * format html for javascript output
     *
     * @return string the parsed string.
     */
    public function render()
    {
        $content = $this->renderChildren();

        // we have to convert the returns
        $content = str_replace('\'', '\\\'', $content);
        $content = str_replace(CHR(10), '\'+' . CHR(10) . '\'', $content);
        $content = str_replace(CHR(13), '\'+' . CHR(13) . '\'', $content);

        return $content;
    }
}
