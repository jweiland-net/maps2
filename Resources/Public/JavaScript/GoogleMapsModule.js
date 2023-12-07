/**
 * Module: TYPO3/CMS/Maps2/GoogleMapsModule
 */
define(["gmaps", "TYPO3/CMS/Backend/Notification"], function($, gmaps, Notification) {
    /**
     * GoogleMaps object
     */
    let GoogleMaps = {
        selector: '#maps2ConfigurationMap',
        record: [],
        extConf: [],
        marker: {},
        map: {},
        infoWindow: {},
        infoWindowContent: {}
    };

    GoogleMaps.createMapOptions = function() {
        return {
            zoom: 14,
            mapTypeId: gmaps.MapTypeId.ROADMAP
        };
    };

    GoogleMaps.createCircleOptions = function(map, record, extConf) {
        let circleOptions = {
            map: map,
            center: new gmaps.LatLng(record.latitude, record.longitude),
            strokeColor: extConf.strokeColor,
            strokeOpacity: extConf.strokeOpacity,
            strokeWeight: extConf.strokeWeight,
            fillColor: extConf.fillColor,
            fillOpacity: extConf.fillOpacity,
            editable: true
        };
        if (record.radius === 0) {
            circleOptions.radius = extConf.defaultRadius;
        } else {
            circleOptions.radius = record.radius;
        }
        return circleOptions;
    };

    GoogleMaps.createPolygonOptions = function(paths, extConf) {
        return {
            paths: paths,
            strokeColor: extConf.strokeColor,
            strokeOpacity: extConf.strokeOpacity,
            strokeWeight: extConf.strokeWeight,
            fillColor: extConf.fillColor,
            fillOpacity: extConf.fillOpacity,
            editable: true
        };
    };

    GoogleMaps.createPolylineOptions = function(paths, extConf) {
        return {
            path: paths,
            strokeColor: extConf.strokeColor,
            strokeOpacity: extConf.strokeOpacity,
            strokeWeight: extConf.strokeWeight,
            editable: true
        };
    };

    GoogleMaps.createMap = function(element) {
        return new gmaps.Map(
            element,
            GoogleMaps.createMapOptions()
        );
    };

    GoogleMaps.createMarker = function(record) {
        GoogleMaps.marker = new gmaps.Marker({
            position: new gmaps.LatLng(record.latitude, record.longitude),
            map: GoogleMaps.map,
            draggable: true
        });

        GoogleMaps.infoWindow.setContent(GoogleMaps.infoWindowContent);

        // open InfoWindow, if marker was clicked.
        GoogleMaps.marker.addListener("click", function() {
            GoogleMaps.infoWindow.open(GoogleMaps.map, GoogleMaps.marker);
        });

        // update fields and marker while dragging
        gmaps.event.addListener(GoogleMaps.marker, 'dragend', function() {
            GoogleMaps.setLatLngFields(
                GoogleMaps.marker.getPosition().lat().toFixed(6),
                GoogleMaps.marker.getPosition().lng().toFixed(6),
                0
            );
        });

        // update fields and marker when clicking on the map
        gmaps.event.addListener(GoogleMaps.map, 'click', function(event) {
            GoogleMaps.marker.setPosition(event.latLng);
            GoogleMaps.setLatLngFields(
                event.latLng.lat().toFixed(6),
                event.latLng.lng().toFixed(6),
                0
            );
        });
    };

    GoogleMaps.createArea = function(record) {
        let coordinatesArray = [];

        if (record.configuration_map) {
            for (let i = 0; i < record.configuration_map.length; i++) {
                coordinatesArray.push(
                    new gmaps.LatLng(
                        record.configuration_map[i].latitude,
                        record.configuration_map[i].longitude
                    )
                );
            }
        }

        if (coordinatesArray.length === 0) {
            coordinatesArray.push(
                new gmaps.LatLng(
                    record.latitude,
                    record.longitude
                )
            );
        }

        let area = new gmaps.Polygon(
            GoogleMaps.createPolygonOptions(coordinatesArray, GoogleMaps.extConf)
        );
        let path = area.getPath();

        area.setMap(GoogleMaps.map);

        // Listener which will be called, if a vertex was moved to a new location
        gmaps.event.addListener(path, 'set_at', function() {
            GoogleMaps.storeRouteAsJson(area);
        });
        // Listener to add new vertex in between a route
        gmaps.event.addListener(path, 'insert_at', function() {
            GoogleMaps.storeRouteAsJson(area);
        });
        // Listener to remove a vertex
        gmaps.event.addListener(area, 'rightclick', function(event) {
            area.getPath().removeAt(event.vertex);
            GoogleMaps.storeRouteAsJson(area);
        });
        // Listener to add a new vertex. Will not be called, while inserting a vertex in between
        gmaps.event.addListener(GoogleMaps.map, 'click', function(event) {
            area.getPath().push(event.latLng);
        });
        // update fields for saving map position
        gmaps.event.addListener(GoogleMaps.map, 'dragend', function() {
            GoogleMaps.setLatLngFields(
                GoogleMaps.map.getCenter().lat().toFixed(6),
                GoogleMaps.map.getCenter().lng().toFixed(6),
                0
            );
        });
    };

    GoogleMaps.createRoute = function(record) {
        let coordinatesArray = [];

        if (record.configuration_map) {
            for (let i = 0; i < record.configuration_map.length; i++) {
                coordinatesArray.push(
                    new gmaps.LatLng(
                        record.configuration_map[i].latitude,
                        record.configuration_map[i].longitude
                    )
                );
            }
        }

        if (coordinatesArray.length === 0) {
            coordinatesArray.push(
                new gmaps.LatLng(
                    record.latitude,
                    record.longitude
                )
            );
        }

        /* create route overlay */
        let route = new gmaps.Polyline(
            GoogleMaps.createPolylineOptions(coordinatesArray, GoogleMaps.extConf)
        );
        let path = route.getPath();

        route.setMap(GoogleMaps.map);

        // Listener which will be called, if a vertex was moved to a new location
        gmaps.event.addListener(path, 'set_at', function() {
            GoogleMaps.storeRouteAsJson(route);
        });
        // Listener to add new vertex in between a route
        gmaps.event.addListener(path, 'insert_at', function() {
            GoogleMaps.storeRouteAsJson(route);
        });
        // Listener to remove a vertex
        gmaps.event.addListener(route, 'rightclick', function(event) {
            route.getPath().removeAt(event.vertex);
            GoogleMaps.storeRouteAsJson(route);
        });
        // Listener to add a new vertex. Will not be called, while inserting a vertex in between
        gmaps.event.addListener(GoogleMaps.map, 'click', function(event) {
            route.getPath().push(event.latLng);
        });
        // update fields for saving map position
        gmaps.event.addListener(GoogleMaps.map, 'dragend', function() {
            GoogleMaps.setLatLngFields(
                GoogleMaps.map.getCenter().lat().toFixed(6),
                GoogleMaps.map.getCenter().lng().toFixed(6),
                0
            );
        });
    };

    GoogleMaps.createRadius = function(record) {
        GoogleMaps.marker = new gmaps.Circle(
            GoogleMaps.createCircleOptions(GoogleMaps.map, record, GoogleMaps.extConf)
        );

        // update fields and marker while dragging
        gmaps.event.addListener(GoogleMaps.marker, 'center_changed', function() {
            GoogleMaps.setLatLngFields(
                GoogleMaps.marker.getCenter().lat().toFixed(6),
                GoogleMaps.marker.getCenter().lng().toFixed(6),
                GoogleMaps.marker.getRadius()
            );
        });

        // update fields and marker while resizing the radius
        gmaps.event.addListener(GoogleMaps.marker, 'radius_changed', function() {
            GoogleMaps.setLatLngFields(
                GoogleMaps.marker.getCenter().lat().toFixed(6),
                GoogleMaps.marker.getCenter().lng().toFixed(6),
                GoogleMaps.marker.getRadius()
            );
        });

        // update fields and marker when clicking on the map
        gmaps.event.addListener(GoogleMaps.map, 'click', function(event) {
            GoogleMaps.marker.setCenter(event.latLng);
            GoogleMaps.setLatLngFields(
                event.latLng.lat().toFixed(6),
                event.latLng.lng().toFixed(6),
                GoogleMaps.marker.getRadius()
            );
        });

        GoogleMaps.setLatLngFields(record.latitude, record.longitude, record.radius);
    };

    /**
     * Fill TCA fields for Lat and Lng with value of marker position
     *
     * @param lat
     * @param lng
     * @param rad
     * @param address
     */
    GoogleMaps.setLatLngFields = function(lat, lng, rad, address) {
        GoogleMaps.setFieldValue("latitude", lat);
        GoogleMaps.setFieldValue("longitude", lng);

        if (typeof rad !== "undefined" && rad > 0) {
            GoogleMaps.setFieldValue("radius", parseInt(rad));
        }

        if (typeof address !== "undefined") {
            GoogleMaps.setFieldValue("address", address);
        }
    };

    /**
     * Generate an uri to save all coordinates
     *
     * @param route
     */
    GoogleMaps.getUriForRoute = function(route) {
        let routeObject = {};
        route.getPath().forEach(function(latLng, index) {
            routeObject[index] = latLng.toUrlValue();
        });
        return routeObject;
    };

    /**
     * Return FieldElement from TCEFORM by fieldName
     *
     * @param field
     * @returns {*|HTMLElement} jQuery object. FormEngine works with $ selectors
     */
    GoogleMaps.getFieldElement = function(field) {
        // Return the FieldElement which is visible to the editor
        return TYPO3.FormEngine.getFieldElement(GoogleMaps.buildFieldName(field), '_list');
    };

    /**
     * Build fieldName like 'data[tx_maps2_domain_model_poicollection][1][latitude]'
     *
     * @param field
     * @returns {string}
     */
    GoogleMaps.buildFieldName = function(field) {
        return 'data[tx_maps2_domain_model_poicollection][' + GoogleMaps.record.uid + '][' + field + ']';
    };

    /**
     * Set field value
     *
     * @param field
     * @param value
     */
    GoogleMaps.setFieldValue = function(field, value) {
        let $fieldElement = GoogleMaps.getFieldElement(field);
        if ($fieldElement && $fieldElement.length) {
            $fieldElement.val(value);
            $fieldElement.triggerHandler("change");
        }
    };

    /**
     * Store route/area path into configuration_map as JSON
     *
     * @param route
     */
    GoogleMaps.storeRouteAsJson = function(route) {
        GoogleMaps.setFieldValue(
            "configuration_map",
            JSON.stringify(GoogleMaps.getUriForRoute(route))
        );
    };

    /**
     * Read address, send it to Google and move map/marker to new location
     */
    GoogleMaps.findAddress = function() {
        let input = document.getElementById("pac-input");
        let autocomplete = new gmaps.places.Autocomplete(input, {fields: ["place_id"]});
        let geoCoder = new gmaps.Geocoder;

        autocomplete.bindTo("bounds", GoogleMaps.map);
        GoogleMaps.map.controls[gmaps.ControlPosition.TOP_LEFT].push(input);

        // Prevent submitting the BE form on enter, while selecting entry from AutoSuggest
        $(input).keydown(function(e) {
            if (e.which === 13 && $(".pac-container:visible").length) return false;
        });

        autocomplete.addListener("place_changed", function() {
            GoogleMaps.infoWindow.close();
            let place = autocomplete.getPlace();

            if (!place.place_id) {
                return;
            }

            geoCoder.geocode({"placeId": place.place_id}, function(results, status) {
                if (status !== "OK") {
                    window.alert("Geocoder failed due to: " + status);
                    return;
                }
                let lat = results[0].geometry.location.lat().toFixed(6);
                let lng = results[0].geometry.location.lng().toFixed(6);

                switch (GoogleMaps.record.collection_type) {
                    case 'Point':
                        //GoogleMaps.marker.setPlace(); // setPlace works, but it resets previous marker settings like draggable, ...
                        GoogleMaps.marker.setPosition(results[0].geometry.location);
                        GoogleMaps.marker.setVisible(true);
                        GoogleMaps.setLatLngFields(lat, lng, 0, results[0].formatted_address);
                        break;
                    case 'Area':
                        GoogleMaps.setLatLngFields(lat, lng, 0, results[0].formatted_address);
                        break;
                    case 'Route':
                        GoogleMaps.setLatLngFields(lat, lng, 0, results[0].formatted_address);
                        break;
                    case 'Radius':
                        GoogleMaps.marker.setCenter(results[0].geometry.location);
                        GoogleMaps.setLatLngFields(lat, lng, GoogleMaps.marker.getRadius(), results[0].formatted_address);
                        break;
                }

                GoogleMaps.map.setCenter(results[0].geometry.location);
                GoogleMaps.infoWindowContent.children["place-name"].textContent = place.name;
                GoogleMaps.infoWindowContent.children["place-id"].textContent = place.place_id;
                GoogleMaps.infoWindowContent.children["place-address"].textContent = results[0].formatted_address;
                GoogleMaps.infoWindow.open(GoogleMaps.map, GoogleMaps.marker);
            });
        });
    };

    GoogleMaps.initialize = function(element, record, extConf) {
        GoogleMaps.record = record;
        GoogleMaps.extConf = extConf;
        GoogleMaps.infoWindow = new gmaps.InfoWindow();
        GoogleMaps.infoWindowContent = document.getElementById("infowindow-content");
        GoogleMaps.map = GoogleMaps.createMap(element);

        if (extConf.googleMapsJavaScriptApiKey === "") {
            Notification.warning(
                'Missing JS API Key',
                'You have forgotten to set Google Maps JavaScript ApiKey in Extension Settings.',
                15
            );
        }

        if (extConf.googleMapsGeocodeApiKey === "") {
            Notification.warning(
                'Missing GeoCode API Key',
                'You have forgotten to set Google Maps Geocode ApiKey in Extension Settings.',
                15
            );
        }

        switch (record.collection_type) {
            case "Point":
                GoogleMaps.createMarker(record);
                break;
            case "Area":
                GoogleMaps.createArea(record);
                break;
            case "Route":
                GoogleMaps.createRoute(record);
                break;
            case "Radius":
                GoogleMaps.createRadius(record);
                break;
        }

        GoogleMaps.findAddress();

        if (record.latitude && record.longitude) {
            GoogleMaps.map.setCenter(new gmaps.LatLng(record.latitude, record.longitude));
        } else {
            // Fallback
            GoogleMaps.map.setCenter(new gmaps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
        }

        // if maps2 was inserted in (bootstrap) tabs, we have to re-render the map
        $("ul.t3js-tabs a[data-toggle='tab']:eq(1)").on("shown.bs.tab", function() {
            google.maps.event.trigger(GoogleMaps.map, "resize");
            if (record.latitude && record.longitude) {
                GoogleMaps.map.setCenter(new gmaps.LatLng(record.latitude, record.longitude));
            } else {
                GoogleMaps.map.setCenter(new gmaps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
            }
        });
    };

    // init if document is ready
    $(document).ready(function() {
        let $googleMaps = $(GoogleMaps.selector);
        if ($googleMaps.length > 0) {
            GoogleMaps.initialize(
                $googleMaps.get(0),
                $googleMaps.data("record"),
                $googleMaps.data("extconf")
            );
        }
    });
});
