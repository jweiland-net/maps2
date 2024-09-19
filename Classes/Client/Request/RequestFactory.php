<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This factory builds new request objects for either Google Maps or Open Street Map.
 * This class only works as long as you keep filenames in GoogleMaps and OpenStreetMap folder in sync.
 */
class RequestFactory
{
    protected array $mapping = [
        'gm' => 'JWeiland\\Maps2\\Client\\Request\\GoogleMaps',
        'osm' => 'JWeiland\\Maps2\\Client\\Request\\OpenStreetMap',
    ];

    protected MapHelper $mapHelper;

    public function __construct(MapHelper $mapHelper)
    {
        $this->mapHelper = $mapHelper;
    }

    /**
     * Create a new Request by its filename
     *
     * @throws \Exception
     */
    public function create(string $filename): RequestInterface
    {
        $className = sprintf(
            '%s\\%s',
            $this->mapping[$this->mapHelper->getMapProvider()],
            $this->sanitizeFilename($filename),
        );

        if (!class_exists($className)) {
            throw new \RuntimeException(sprintf(
                'Class "%s" to create a new Request could not be found',
                $className,
            ));
        }

        /** @var RequestInterface $request */
        $request = GeneralUtility::makeInstance($className);

        return $request;
    }

    protected function sanitizeFilename(string $filename): string
    {
        return ucfirst(GeneralUtility::split_fileref($filename)['filebody']);
    }
}
