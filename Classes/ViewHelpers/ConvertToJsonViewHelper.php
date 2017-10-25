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

use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
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
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * implements a ViewHelper to convert an array into JSON format
     *
     * @return array
     */
    public function render()
    {
        $value = $this->renderChildren();
        if ($value instanceof PoiCollection) {
            $json = $this->getPoiCollectionsAsJson(array($value));
        } elseif (
            $value instanceof QueryResultInterface ||
            $value instanceof ObjectStorage ||
            $value instanceof \SplObjectStorage ||
            is_array($value)
        ) {
            $json = $this->getPoiCollectionsAsJson($value);
        } else {
            $json = '{}';
        }

        return htmlspecialchars($json);
    }

    /**
     * Convert poiCollections to array and pass them through json_encode
     *
     * @param array $poiCollections
     * @return string
     */
    protected function getPoiCollectionsAsJson($poiCollections)
    {
        $poiCollectionsAsArray = array();
        /** @var PoiCollection $poiCollection */
        foreach ($poiCollections as $poiCollection) {
            if ($poiCollection instanceof PoiCollection) {
                $poiCollectionAsArray = ObjectAccess::getGettableProperties($poiCollection);

                /** @var LazyObjectStorage $pois */
                $pois = $poiCollectionAsArray['pois'];
                $poiCollectionAsArray['pois'] = array();
                /** @var Poi $poi */
                foreach ($pois->toArray() as $key => $poi) {
                    // do not remove toArray() as it converts the long hash keys to 0, 1, 2, ...
                    $poiCollectionAsArray['pois'][$key] = ObjectAccess::getGettableProperties($poi);
                }

                $poiCollectionAsArray['categories'] = array();
                /** @var Category $category */
                foreach ($poiCollection->getCategories() as $category) {
                    $poiCollectionAsArray['categories'][] = ObjectAccess::getGettableProperties($category);
                }
                $poiCollectionsAsArray[] = $poiCollectionAsArray;
            } else {
                // if array does not consists of PoiCollections pass it through json_encode and return directly
                return json_encode($poiCollections);
            }
        }
        return json_encode($poiCollectionsAsArray);
    }
}
