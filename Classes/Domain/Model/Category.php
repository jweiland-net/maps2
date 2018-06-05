<?php
namespace JWeiland\Maps2\Domain\Model;

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
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class Category
 *
 * @category Domain/Model
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * markerIcon
     *
     * @var string
     */
    protected $markerIcon = '';

    /**
     * Sorting
     *
     * @var int
     */
    protected $sorting = 0;

    /**
     * Returns the markerIcon
     *
     * @return string $markerIcon
     */
    public function getMarkerIcon()
    {
        if (!empty($this->markerIcon)) {
            $absFile = GeneralUtility::getFileAbsFileName($this->markerIcon);
            if (is_file($absFile)) {
                return PathUtility::getAbsoluteWebPath($absFile);
            }
        }
        return '';
    }

    /**
     * Sets the markerIcon
     *
     * @param string $markerIcon
     * @return void
     */
    public function setMarkerIcon($markerIcon)
    {
        $this->markerIcon = (string)trim($markerIcon);
    }

    /**
     * Returns the sorting
     *
     * @return int $sorting
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param int $sorting
     *
     * @return void
     */
    public function setSorting($sorting)
    {
        $this->sorting = (int)$sorting;
    }
}
