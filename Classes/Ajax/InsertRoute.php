<?php
namespace JWeiland\Maps2\Ajax;

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
use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;

/**
 * Class InsertRoute
 *
 * @category Ajax
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class InsertRoute extends AbstractAjaxRequest
{

    /**
     * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * inject poiCollectionRepository
     *
     * @param \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository
     * @return void
     */
    public function injectPoiCollectionRepository(
        \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository
    ) {
        $this->poiCollectionRepository = $poiCollectionRepository;
    }

    /**
     * process ajax request
     *
     * @param array $arguments Arguments to process
     * @param string $hash A generated hash value to verify that there are no modifications in the uri
     * @return string
     */
    public function processAjaxRequest(array $arguments, $hash)
    {
        // cast arguments
        $uid = (int)$arguments['uid'];
        $route = (array)$arguments['route'];

        $poiCollection = $this->poiCollectionRepository->findByUid($uid);

        if ($poiCollection instanceof PoiCollection) {
            // validate uri arguments
            if (!$this->validateArguments($poiCollection, $hash)) {
                return 'arguments are not valid';
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
     * get updated position records
     * this method loops through all route positions and insert or updates the expected record in db
     *
     * @param PoiCollection $poiCollection The parent object for pois
     * @param array $routes Array containing all positions of the route
     * @return PoiCollection A collection of position records
     */
    public function getUpdatedPositionRecords(PoiCollection $poiCollection, array $routes)
    {
        if (count($routes)) {
            foreach ($routes as $posIndex => $route) {
                // get latitude and longitude from current route
                $latLng = explode(',', $route);
                $lat = (float)$latLng[0];
                $lng = (float)$latLng[1];

                // check if we have such a record already
                $poi = $this->getPoiFromPoiArray($poiCollection, $posIndex);

                if ($poi instanceof Poi) {
                    // update poi if lat or lng differs
                    if ($poi->getLatitude() != $lat || $poi->getLongitude() != $lng) {
                        $poiCollection->getPois()->detach($poi);
                        $poi->setLatitude($lat);
                        $poi->setLongitude($lng);
                        $poiCollection->getPois()->attach($poi);
                    }
                } else {
                    // create a new poi
                    /** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
                    $poi = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Model\\Poi');

                    // TODO set cruser_id
                    $poi->setPid($poiCollection->getPid());
                    $poi->setLatitude($lat);
                    $poi->setLongitude($lng);
                    $poi->setPosIndex($posIndex);

                    $poiCollection->getPois()->attach($poi);
                }
            }
            // check if points were removed
            $amountOfRoutes = count($routes);
            if ($amountOfRoutes < count($poiCollection->getPois())) {
                $poi = $this->getPoiFromPoiArray($poiCollection, $amountOfRoutes);
                if ($poi instanceof Poi) {
                    $poiCollection->getPois()->detach($poi);
                }
            }
        }

        return $poiCollection;
    }

    /**
     * get poi from poi array
     *
     * @param PoiCollection $poiCollection
     * @param $posIndex
     * @return null|Poi
     */
    public function getPoiFromPoiArray(PoiCollection $poiCollection, $posIndex)
    {
        /** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
        foreach ($poiCollection->getPois() as $poi) {
            if ($poi->getPosIndex() == $posIndex) {
                return $poi;
            }
        }
        return null;
    }
}
