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

use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;

/**
 * Ajax request class to insert a new Route
 */
class InsertRoute extends AbstractAjaxRequest
{
    /**
     * @var PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * inject poiCollectionRepository
     *
     * @param PoiCollectionRepository $poiCollectionRepository
     */
    public function injectPoiCollectionRepository(
        PoiCollectionRepository $poiCollectionRepository
    ) {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * process ajax request
     *
     * @param array $arguments Arguments to process
     * @param string $hash A generated hash value to verify that there are no modifications in the uri
     * @return string
     * @throws \Exception
     */
    public function processAjaxRequest(array $arguments, string $hash): string
    {
        // cast arguments
        $uid = (int)$arguments['uid'];
        $route = (array)$arguments['route'];

        $poiCollection = $this->poiCollectionRepository->findByUid($uid);
        if ($poiCollection instanceof PoiCollection) {
            // validate uri arguments
            if (!$this->validateArguments($poiCollection, $hash)) {
                return 'Arguments are not valid';
            }

            $poiCollection = $this->getUpdatedPositionRecords($poiCollection, $route);

            $this->poiCollectionRepository->update($poiCollection);
            $this->persistenceManager->persistAll();
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * Get updated position records
     * this method loops through all route positions and insert or updates the expected record in db
     *
     * @param PoiCollection $poiCollection The parent object for pois
     * @param array $routes Array containing all positions of the route
     * @return PoiCollection A collection of position records
     */
    public function getUpdatedPositionRecords(PoiCollection $poiCollection, array $routes)
    {
        if (count($routes)) {
            $this->removeAllPois($poiCollection);
            foreach ($routes as $posIndex => $route) {
                // get latitude and longitude from current route
                $latLng = explode(',', $route);
                $lat = (float)$latLng[0];
                $lng = (float)$latLng[1];

                // create new poi
                $poi = $this->objectManager->get(Poi::class);
                $poi->setPid($poiCollection->getPid());
                $poi->setLatitude($lat);
                $poi->setLongitude($lng);
                $poi->setPosIndex($posIndex);

                $poiCollection->getPois()->attach($poi);
            }
        }

        return $poiCollection;
    }

    /**
     * Instead of checking each POI for existence,
     * it's easier to remove all and create them from scratch
     *
     * @param PoiCollection $poiCollection
     * @return void
     */
    protected function removeAllPois(PoiCollection $poiCollection)
    {
        $pois = clone $poiCollection->getPois();
        $poiCollection->getPois()->removeAll($pois);
    }
}
