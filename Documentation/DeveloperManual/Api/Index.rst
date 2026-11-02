..  include:: /Includes.rst.txt


..  _developer-api:

=========
Maps2 API
=========

Maps2 comes with some public methods which we have marked with ``@api``. Use
them to simplify your life within your extensions when working with maps2.

Some of these methods work with Google Geocode API. So please check, if you have
setup API Key correctly in extension manager configuration of maps2.

Methods
=======

getFirstFoundPositionByAddress
------------------------------

Give it an address (string) as first argument and you will get first found
address as Position object. If Google Geocode has nothing found or an error
occurs this method will return ``null``. There is no need to PHP:rawurlencode
the address as we will do it for you.

getPositionsByAddress
---------------------

Give it an address (string) as first argument and you will get all found results
from Google Geocode as RadiusResult in an ObjectStorage.

..  important::

    Within the last years Google Geocode has changed something. It will always
    return exactly ONE result. So, in that case it may not make sense to call
    that method and switch
    to :ref:`getFirstFoundPositionByAddress <getFirstFoundPositionByAddress>`
    above.

getGeoCodeResult
----------------

Same as ``getPositionsByAddress``, but returns a ``GeoCodeResult`` value object
containing the found positions or the error, why the address could not be
geocoded. Use it, if you want to show a meaningful message to your users.

``$result->error->type`` is a case of the provider independent enum
``\JWeiland\Maps2\Client\ClientErrorEnum``:

``NO_POSITIONS_FOUND``
    The map provider did not find the address. Ask the user to check the
    input.

``REQUEST_DENIED``
    The map provider rejected the request, e.g. because of an invalid API key or
    because the API key is restricted to other IP addresses.

``QUOTA_EXCEEDED``
    The request limit or the quota of the map provider was exceeded.

``INVALID_REQUEST``
    The map provider did not accept the request.

``INVALID_RESPONSE``
    The response of the map provider is not valid JSON.

``SERVICE_ERROR``
    The map provider could not be reached or answered with an unknown error.

Only ``NO_POSITIONS_FOUND`` is caused by the user input. ``$result->error->message``
contains the technical message of the map provider. Please don't show it to
frontend users, as it may contain sensitive data like the IP address of your
server.

..  code-block:: php

    $result = $this->geoCodeService->getGeoCodeResult($address);
    $position = $result->getFirstPosition();
    if ($position === null) {
        $message = match ($result->error?->type) {
            ClientErrorEnum::NO_POSITIONS_FOUND => 'Please check your address',
            default => 'Address lookup is currently not possible',
        };
    }

createNewPoiCollection
----------------------

Use it, if you have some location records in your extension and want to create
a new PoiCollection relation automatically while saving your location record.

$pid (int)
~~~~~~~~~~

At which page (Table: pages) should we save the PoiCollection record for you?

$position (Position)
~~~~~~~~~~~~~~~~~~~~

To save a PoiCollection we need the latitude and longitude. It is good practise
to retrieve such a Position from getFirstFoundPositionByAddress above.

$overrideFieldValues (array)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We will prefill some fields like ``title`` with the formatted address of given
``Position`` object. You want your own values? Use ``overrideFieldValues``
to override our prefilled values. That way you can override every field of
table ``tx_maps2_domain_model_poicollection``. We have added a check against
available fields in table. That way it is not possible to produce an invalid
INSERT query.

Return value (int)
~~~~~~~~~~~~~~~~~~

The UID of the just created PoiCollection record

assignPoiCollectionToForeignRecord
----------------------------------

We have over 5 extensions working together with maps2 and we always have to
implement a part to save the PoiCollection UID to our other extensions. Wouldn't
it be better to have a centralized method doing that? Yes! So here are the
Arguments you should fill:

$poiCollectionUid (int)
~~~~~~~~~~~~~~~~~~~~~~~

Fill it with the PoiCollection UID you will get from ``createNewPoiCollection``.

$foreignRecord (array)
~~~~~~~~~~~~~~~~~~~~~~

This is a table row of your extension. We use it as PHP:reference to add the
new PoiCollection UID directly into your foreign record.

$foreignTableName (string)
~~~~~~~~~~~~~~~~~~~~~~~~~~

As we save the foreign extension record for you, we need the tablename where to
save the record.

$foreignFieldName (string)
~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: tx_maps2_uid

As we save the foreign extension record for you, we need the fieldname where
to save the UID of PoiCollection.

getColumnRegistry
-----------------

Get the Maps2 registry as array for all tables, columns and its configuration

getColumnRegistry
-----------------

addForeignRecordsToPoiCollection
--------------------------------

This method is NOT public API. It will be called automatically, if you call
getForeignRecords of a PoiCollection. It contains a SignalSlot where you can
remove or modify foreignRecords before adding them to PoiCollection.

Example
=======

Here we have a working example out of our extension events2:

..  code-block:: php

    // create new map-record and set it in relation
    $position = $this->googleMapsService->getFirstFoundPositionByAddress($this->getAddress($eventLocation));
    if ($position instanceof Position) {
        $tsConfig = $this->getTsConfig($eventLocation);
        $this->googleMapsService->assignPoiCollectionToForeignRecord(
            $this->googleMapsService->createNewPoiCollection(
                (int)$tsConfig['pid'],
                $position,
                array(
                    'title' => $eventLocation['location']
                )
            ),
            $eventLocation,
            'tx_events2_domain_model_location',
            'tx_maps2_uid'
        );
    }
