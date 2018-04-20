<?php
namespace JWeiland\Maps2\Ajax;

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

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;

/**
 * Class ModifyMarker
 *
 * @category Ajax
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class ModifyMarker extends AbstractAjaxRequest
{
    /**
     * @var PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * inject poiCollectionRepository
     *
     * @param PoiCollectionRepository $poiCollectionRepository
     * @return void
     */
    public function injectPoiCollectionRepository(PoiCollectionRepository $poiCollectionRepository) {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * process ajax request
     *
     * @param array $arguments Arguments to process
     * @param string $hash A generated hash value to verify that there are no modifications in the uri
     *
     * @return string
     *
     * @throws \Exception
     */
    public function processAjaxRequest(array $arguments, $hash)
    {
        // cast arguments
        $uid = (int)$arguments['uid'];
        $lat = (float)$arguments['coords']['latitude'];
        $lng = (float)$arguments['coords']['longitude'];

        $poiCollection = $this->poiCollectionRepository->findByUid($uid);

        if ($poiCollection instanceof PoiCollection) {
            // validate uri arguments
            if (!$this->validateArguments($poiCollection, $hash)) {
                return 'arguments are not valid';
            }

            $poiCollection = $this->updateMarker($poiCollection, $lat, $lng);
            $this->poiCollectionRepository->update($poiCollection);
            $this->persistenceManager->persistAll();
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * Update a given poi
     * In this case the original cols for lat and lng were not set
     *
     * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection The poiCollection to update
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return \JWeiland\Maps2\Domain\Model\PoiCollection
     */
    public function updateMarker(PoiCollection $poiCollection, $lat, $lng)
    {
        $poiCollection->setLatitude($lat);
        $poiCollection->setLongitude($lng);

        // save original poi position, when user has pressed "update" button for field address
        $poiCollection->setLatitudeOrig($lat);
        $poiCollection->setLongitudeOrig($lng);

        return $poiCollection;
    }
}
