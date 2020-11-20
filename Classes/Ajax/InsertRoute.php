<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Ajax;

use TYPO3\CMS\Core\Database\Connection;

/**
 * Ajax request class to insert a new Route
 */
class InsertRoute extends AbstractAjaxRequest
{
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

        $poiCollection = $this->getPoiCollection($uid);
        if (!empty($poiCollection)) {
            // validate URI-arguments against hash
            if (!$this->validateArguments($poiCollection, $hash)) {
                return 'Arguments are not valid';
            }
            $this->updatePoiRecordsOfPoiCollection($poiCollection, $route);
            return '1';
        }
        return '0';
    }

    /**
     * Delete all related POI records of given PoiCollection and rebuild them from scratch.
     *
     * @param array $poiCollection The POI collection record
     * @param array $routes Array containing all positions of the route
     */
    public function updatePoiRecordsOfPoiCollection(array $poiCollection, array $routes)
    {
        if (!empty($routes)) {
            $this->removeAllRelatedPoiRecords((int)$poiCollection['uid']);
            $newPoiRecords = [];
            foreach ($routes as $posIndex => $route) {
                $newPoiRecords[] = $this->buildPoiRecord($poiCollection, $route, (int)$posIndex);
            }

            $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poi');
            $connection->bulkInsert(
                'tx_maps2_domain_model_poi',
                $newPoiRecords,
                [
                    'pid', 'poicollection', 'crdate', 'tstamp', 'cruser_id', 'latitude', 'longitude', 'pos_index'
                ],
                [
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_STR, // there is no PARAM_FLOAT
                    Connection::PARAM_STR, // there is no PARAM_FLOAT
                    Connection::PARAM_INT,
                ]
            );

            // Update amount a related POI records in PoiCollection record
            $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poi');
            $connection->update(
                'tx_maps2_domain_model_poicollection',
                [
                    'pois' => count($newPoiRecords)
                ],
                [
                    'uid' => (int)$poiCollection['uid']
                ]
            );
        }
    }

    /**
     * Build a new POI record
     *
     * @param array $poiCollection The POI collection record
     * @param string $route Comma separated string containing lat and lng
     * @param int $posIndex The ordering position index of the new POI record
     * @return array
     */
    protected function buildPoiRecord(array $poiCollection, string $route, int $posIndex): array
    {
        // Get latitude and longitude from current route
        $latLng = explode(',', $route);
        $latitude = (float)$latLng[0];
        $longitude = (float)$latLng[1];

        // create new POI
        return [
            'pid' => $poiCollection['pid'],
            'poicollection' => $poiCollection['uid'],
            'crdate' => time(),
            'tstamp' => time(),
            'cruser_id' => $GLOBALS['BE_USER']->user['uid'],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'pos_index' => $posIndex
        ];
    }

    /**
     * Instead of checking each POI for existence,
     * it's easier to remove all and create them from scratch
     *
     * @param int $poiCollectionUid
     */
    protected function removeAllRelatedPoiRecords(int $poiCollectionUid)
    {
        // Remove all related POI records
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poi');
        $connection->delete(
            'tx_maps2_domain_model_poi',
            [
                'poicollection' => $poiCollectionUid
            ]
        );

        // Update amount of POIs in parent record to 0
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poicollection');
        $connection->update(
            'tx_maps2_domain_model_poicollection',
            [
                'pois' => 0
            ],
            [
                'uid' => $poiCollectionUid
            ]
        );
    }
}
