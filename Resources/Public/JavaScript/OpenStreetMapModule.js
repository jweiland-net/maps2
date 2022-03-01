/**
 * Module: TYPO3/CMS/Maps2/OpenStreetMapModule
 */
define("TYPO3/CMS/Maps2/OpenStreetMapModule", ["jquery", "leaflet", "leafletDragPath", "leafletEditable"], function($, L) {
    let initialize = function(element, record, extConf) {
        let marker = {};
        let map = {};

        let createMap = function () {
            map = L.map(
                element,
                {
                    editable: true
                }).setView([51.505, -0.09], 15);

            L.tileLayer(location.protocol + '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                id: 'mapbox.streets'
            }).addTo(map);
        };

        createMap();

        let createMarker = function() {
            marker = L.marker(
                [record.latitude, record.longitude],
                {
                    'draggable': true
                }
            ).addTo(map);

            // update fields and marker while dragging
            marker.on('dragend', function() {
                setLatLngFields(
                    marker.getLatLng().lat.toFixed(6),
                    marker.getLatLng().lng.toFixed(6),
                    0
                );
            });

            // update fields and marker when clicking on the map
            map.on('click', function(event) {
                marker.setLatLng(event.latlng);
                setLatLngFields(
                    event.latlng.lat.toFixed(6),
                    event.latlng.lng.toFixed(6),
                    0
                );
            });
        };

        let createArea = function() {
            let area = {};
            let coordinatesArray = [];
            let options = {
                color: extConf.strokeColor,
                weight: extConf.strokeWeight,
                opacity: extConf.strokeOpacity,
                fillColor: extConf.fillColor,
                fillOpacity: extConf.fillOpacity
            };

            if (record.configuration_map) {
                for (let i = 0; i < record.configuration_map.length; i++) {
                    coordinatesArray.push([
                        record.configuration_map[i].latitude,
                        record.configuration_map[i].longitude]
                    );
                }
            }

            if (coordinatesArray.length === 0) {
                area = map.editTools.startPolygon(null, options);
            } else {
                area = L.polygon(coordinatesArray, options).addTo(map);
                area.enableEdit();
            }

            map.on('moveend', function(event) {
                setLatLngFields(
                    event.target.getCenter().lat.toFixed(6),
                    event.target.getCenter().lng.toFixed(6),
                    0
                );
            });
            map.on("editable:vertex:new", function(event) {
                storeRouteAsJson(area.getLatLngs()[0]);
            });
            map.on("editable:vertex:deleted", function(event) {
                storeRouteAsJson(area.getLatLngs()[0]);
            });
            map.on("editable:vertex:dragend", function(event) {
                storeRouteAsJson(area.getLatLngs()[0]);
            });
        };

        let createRoute = function() {
            let route = {};
            let coordinatesArray = [];
            let options = {
                color: extConf.strokeColor,
                weight: extConf.strokeWeight,
                opacity: extConf.strokeOpacity
            };

            if (record.configuration_map) {
                for (let i = 0; i < record.configuration_map.length; i++) {
                    coordinatesArray.push([
                        record.configuration_map[i].latitude,
                        record.configuration_map[i].longitude]
                    );
                }
            }

            if (coordinatesArray.length === 0) {
                route = map.editTools.startPolyline(null, options);
            } else {
                route = L.polyline(coordinatesArray, options).addTo(map);
                route.enableEdit();
            }

            map.on('moveend', function(event) {
                setLatLngFields(
                    event.target.getCenter().lat.toFixed(6),
                    event.target.getCenter().lng.toFixed(6),
                    0
                );
            });
            map.on("editable:vertex:new", function(event) {
                storeRouteAsJson(route.getLatLngs());
            });
            map.on("editable:vertex:deleted", function(event) {
                storeRouteAsJson(route.getLatLngs());
            });
            map.on("editable:vertex:dragend", function(event) {
                storeRouteAsJson(route.getLatLngs());
            });
        };

        let createRadius = function() {
            marker = L.circle(
                [record.latitude, record.longitude],
                {
                    color: extConf.strokeColor,
                    opacity: extConf.strokeOpacity,
                    weight: extConf.strokeWeight,
                    fillColor: extConf.fillColor,
                    fillOpacity: extConf.fillOpacity,
                    radius: record.radius ? record.radius : extConf.defaultRadius
                }
            ).addTo(map);
            marker.enableEdit();

            // update fields and marker while dragging
            marker.on('editable:vertex:dragend', function(event) {
                setLatLngFields(
                    marker.getLatLng().lat.toFixed(6),
                    marker.getLatLng().lng.toFixed(6),
                    marker.getRadius()
                );
            });
        };

        /**
         * Fill TCA fields for Lat and Lng with value of marker position
         *
         * @param lat
         * @param lng
         * @param rad
         * @param address
         */
        let setLatLngFields = function(lat, lng, rad, address) {
            setFieldValue("latitude", lat);
            setFieldValue("longitude", lng);

            if (typeof rad !== "undefined" && rad > 0) {
                setFieldValue("radius", parseInt(rad));
            }

            if (typeof address !== "undefined") {
                setFieldValue("address", address);
            }
        };

        /**
         * Generate an uri to save all coordinates
         *
         * @param coordinates
         */
        let getUriForCoordinates = function(coordinates) {
            let routeObject = {};
            for (let index = 0; index < coordinates.length; index++) {
                routeObject[index] = coordinates[index]['lat'] + ',' + coordinates[index]['lng'];
            }
            return routeObject;
        };

        /**
         * Return FieldElement from TCEFORM by fieldName
         *
         * @param field
         * @returns {*|HTMLElement} jQuery object. FormEngine works with $ selectors
         */
        let getFieldElement = function(field) {
            // Return the FieldElement which is visible to the editor
            return TYPO3.FormEngine.getFieldElement(buildFieldName(field), '_list');
        };

        /**
         * Build fieldName like 'data[tx_maps2_domain_model_poicollection][1][latitude]'
         *
         * @param field
         * @returns {string}
         */
        let buildFieldName = function(field) {
            return 'data[tx_maps2_domain_model_poicollection][' + record.uid + '][' + field + ']';
        };

        /**
         * Set field value
         *
         * @param field
         * @param value
         */
        let setFieldValue = function(field, value) {
            let $fieldElement = getFieldElement(field);
            if ($fieldElement && $fieldElement.length) {
                $fieldElement.val(value);
                $fieldElement.triggerHandler("change");
            }
        };

        /**
         * Store route/area path into configuration_map as JSON
         *
         * @param coordinates
         */
        let storeRouteAsJson = function(coordinates) {
            setFieldValue(
                "configuration_map",
                JSON.stringify(getUriForCoordinates(coordinates))
            );
        };

        /**
         * read address, send it to OpenStreetMap and move map/marker to new location
         */
        let findAddress = function() {
            let $pacSearch = $(document.getElementById("pac-search"));

            // Prevent submitting the BE form on enter
            $pacSearch.keydown(function (event) {
                if (event.which === 13) {
                    if ($pacSearch.val()) {
                        $.ajax({
                            type: "GET",
                            url: 'https://nominatim.openstreetmap.org/search?q=' + encodeURI($pacSearch.val()) + '&format=json&addressdetails=1',
                            dataType: 'json'
                        }).done(function(data) {
                            if (data.length === 0) {
                                alert('Address not found');
                            } else {
                                let lat = parseFloat(data[0].lat).toFixed(6);
                                let lng = parseFloat(data[0].lon).toFixed(6);
                                let address = data[0].address;
                                let formattedAddress = getFormattedAddress(address);

                                switch (record.collection_type) {
                                    case 'Point':
                                        marker.setLatLng([lat, lng]);
                                        setLatLngFields(lat, lng, 0, formattedAddress);
                                        break;
                                    case 'Area':
                                        setLatLngFields(lat, lng, 0, formattedAddress);
                                        break;
                                    case 'Route':
                                        setLatLngFields(lat, lng, 0, formattedAddress);
                                        break;
                                    case 'Radius':
                                        marker.setLatLng([lat, lng]);
                                        setLatLngFields(lat, lng, marker.getRadius(), formattedAddress);
                                        break;
                                }

                                map.panTo([lat, lng]);
                            }
                        }).fail(function() {
                            // alert("Shit");
                        });
                    }

                    return false;
                }
            });
        };

        /**
         * format address from ajax result
         *
         * @param address
         * @returns {string}
         */
        let getFormattedAddress = function (address) {
            let formattedAddress = '';
            let city = '';

            if (address.hasOwnProperty('road')) {
                formattedAddress += address.road;
            }
            if (address.hasOwnProperty('house_number')) {
                formattedAddress += ' ' + address.house_number;
            }
            if (address.hasOwnProperty('postcode')) {
                formattedAddress += ', ' + address.postcode;
            }

            if (address.hasOwnProperty('village')) {
                city = address.village;
            }
            if (address.hasOwnProperty('town')) {
                city = address.town;
            }
            if (address.hasOwnProperty('city')) {
                city = address.city;
            }
            formattedAddress += ' ' + city;

            if (address.hasOwnProperty('country')) {
                formattedAddress += ', ' + address.country;
            }

            return formattedAddress;
        };

        switch (record.collection_type) {
            case "Point":
                createMarker();
                break;
            case "Area":
                createArea();
                break;
            case "Route":
                createRoute();
                break;
            case "Radius":
                createRadius();
                break;
        }

        findAddress();

        if (record.latitude && record.longitude) {
            map.panTo([record.latitude, record.longitude]);
        } else {
            // Fallback
            map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
        }

        // if maps2 was inserted in (bootstrap) tabs, we have to re-render the map
        $("ul.t3js-tabs a[data-toggle='tab']:eq(1)").on("shown.bs.tab", function() {
            map.invalidateSize();
            if (record.latitude && record.longitude) {
                map.panTo([record.latitude, record.longitude]);
            } else {
                // Fallback
                map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
            }
        });
    };

    /**
     * Return a function that gets DOM elements that are checked if suggest is already initialized
     * @exports TYPO3/CMS/Backend/FormEngineSuggest
     */
    return function() {
        $element = $("#maps2ConfigurationMap");
        initialize(
            $element.get(0),
            $element.data("record"),
            $element.data("extconf")
        );
    };
});
