<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

use JWeiland\Maps2\Configuration\ExtConf;
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
     * @param ExtConf $extConf
     * @return RequestInterface
     * @throws \Exception
     */
    public function create(string $filename, ExtConf $extConf = null): RequestInterface
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
        $request = GeneralUtility::makeInstance($className, $extConf);

        return $request;
    }

    protected function sanitizeFilename(string $filename): string
    {
        return ucfirst(GeneralUtility::split_fileref($filename)['filebody']);
    }
}
