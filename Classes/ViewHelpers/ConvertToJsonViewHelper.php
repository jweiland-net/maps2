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
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * As we need many information in frontend, this ViewHelper is really helpful to
 * convert all array and object types into a json string which we/you can use for various data attributes.
 */
class ConvertToJsonViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Implements a ViewHelper to convert an array into JSON format
     */
    public function render(): string
    {
        $poiCollections = $this->renderChildren();

        if ($poiCollections instanceof PoiCollection) {
            $poiCollections = [$poiCollections];
        }

        if ($this->valueContainsPoiCollections($poiCollections)) {
            $json = $this->getPoiCollectionsAsJson($poiCollections);
        } else {
            $json = json_encode($poiCollections);
        }

        return htmlspecialchars($json);
    }

    /**
     * Convert poiCollections to array and pass them through json_encode
     *
     * @param array $poiCollections
     * @return string
     */
    protected function getPoiCollectionsAsJson($poiCollections): string
    {
        $poiCollectionsAsArray = [];
        /** @var PoiCollection $poiCollection */
        foreach ($poiCollections as $poiCollection) {
            $poiCollectionAsArray = ObjectAccess::getGettableProperties($poiCollection);
            unset($poiCollectionAsArray['markerIcons']);

            /** @var LazyObjectStorage $pois */
            $pois = $poiCollectionAsArray['pois'];
            $poiCollectionAsArray['pois'] = [];
            /** @var Poi $poi */
            foreach ($pois->toArray() as $key => $poi) {
                // do not remove toArray() as it converts the long hash keys to 0, 1, 2, ...
                $poiCollectionAsArray['pois'][$key] = ObjectAccess::getGettableProperties($poi);
            }

            $poiCollectionAsArray['categories'] = [];
            /** @var Category $category */
            foreach ($poiCollection->getCategories() as $category) {
                $categoryProperties = ObjectAccess::getGettableProperties($category);
                unset($categoryProperties['maps2MarkerIcons']);
                unset($categoryProperties['parent']);
                $poiCollectionAsArray['categories'][] = $categoryProperties;
            }
            $poiCollectionsAsArray[] = $poiCollectionAsArray;
        }
        return json_encode($poiCollectionsAsArray);
    }

    /**
     * Check, if value contains entries of type PoiCollection
     *
     * @param mixed $value
     * @return bool
     */
    protected function valueContainsPoiCollections($value): bool
    {
        $containsPoiCollections = false;
        if (is_array($value)) {
            reset($value);
            $poiCollection = current($value);
            $containsPoiCollections = $poiCollection instanceof PoiCollection;
        } elseif ($value instanceof \Iterator) {
            $value->rewind();
            $poiCollection = $value->current();
            $containsPoiCollections = $poiCollection instanceof PoiCollection;
        }

        return $containsPoiCollections;
    }
}
