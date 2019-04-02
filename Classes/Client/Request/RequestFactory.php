<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Client\Request;

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

use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This factory builds new request objects for either Google Maps or Open Street Map.
 * This class only works as long as you keep filenames in GoogleMaps and OpenStreetMap folder in sync.
 */
class RequestFactory
{
    /**
     * @var array
     */
    protected $mapping = [
        'gm' => 'JWeiland\\Maps2\\Client\\Request\\GoogleMaps',
        'osm' => 'JWeiland\\Maps2\\Client\\Request\\OpenStreetMap'
    ];

    /**
     * Create a new Request by its filename
     *
     * @param string $filename Filename to build the Request object
     * @return RequestInterface
     * @throws \Exception
     */
    public function create(string $filename): RequestInterface
    {
        $mapService = GeneralUtility::makeInstance(MapService::class);
        $className = sprintf(
            '%s\\%s',
            $this->mapping[$mapService->getMapProvider()],
            $this->sanitizeFilename($filename)
        );

        if (!class_exists($className)) {
            throw new \Exception(sprintf(
                'Class "%s" to create a new Request could not be found',
                $className
            ));
        }

        /** @var RequestInterface $request */
        $request = GeneralUtility::makeInstance($className);

        return $request;
    }

    /**
     * Sanitizes the filename
     * We need UpperCamelCase
     * We only need Filename body
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        return ucfirst(GeneralUtility::split_fileref($filename)['filebody']);
    }
}
