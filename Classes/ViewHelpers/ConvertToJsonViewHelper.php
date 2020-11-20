<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * As we need many information in frontend, this ViewHelper is really helpful to
 * convert all array and object types into a json string which we/you can use for various data attributes.
 */
class ConvertToJsonViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $poiCollections = $renderChildrenClosure();

        if ($poiCollections instanceof PoiCollection) {
            $poiCollections = [$poiCollections];
        }

        if (self::valueContainsPoiCollections($poiCollections)) {
            $json = self::getPoiCollectionsAsJson($poiCollections);
        } else {
            $json = json_encode($poiCollections);
        }

        return htmlspecialchars($json);
    }

    /**
     * Convert poiCollections to array and pass them through json_encode
     *
     * @param array|QueryResultInterface|ObjectStorage|PoiCollection[] $poiCollections
     * @return string
     */
    protected static function getPoiCollectionsAsJson($poiCollections): string
    {
        $poiCollectionsAsArray = [];
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
    protected static function valueContainsPoiCollections($value): bool
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
