/**
 * Module: TYPO3/CMS/Maps2/GoogleMapsModule
 */
define("TYPO3/CMS/Maps2/OpenStreetMapModule", ["jquery", "leaflet", "leafletDragPath", "leafletEditable"], function($, L) {
    var initialize = function(element, config, extConf) {
        var marker = {};
        var map = {};

        var createMap = function () {
            map = L.map(
                element,
                {
                    editable: true
                }).setView([51.505, -0.09], 15);

            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                id: 'mapbox.streets'
            }).addTo(map);
        };

        createMap();

        /**
         * Create Marker
         */
        var createMarker = function() {
            marker = L.marker(
                [config.latitude, config.longitude],
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

        /**
         * Create Area
         */
        var createArea = function() {
            var area = {};
            var coordinatesArray = [];
            var options = {
                color: extConf.strokeColor,
                weight: extConf.strokeWeight,
                opacity: extConf.strokeOpacity,
                fillColor: extConf.fillColor,
                fillOpacity: extConf.fillOpacity
            };

            if (typeof config.pois !== 'undefined') {
                for (var i = 0; i < config.pois.length; i++) {
                    coordinatesArray.push([config.pois[i].latitude, config.pois[i].longitude]);
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
                insertRouteToDb(area.getLatLngs()[0]);
            });
            map.on("editable:vertex:deleted", function(event) {
                insertRouteToDb(area.getLatLngs()[0]);
            });
            map.on("editable:vertex:dragend", function(event) {
                insertRouteToDb(area.getLatLngs()[0]);
            });
        };

        /**
         * Create Route
         */
        var createRoute = function() {
            var route = {};
            var coordinatesArray = [];
            var options = {
                color: extConf.strokeColor,
                weight: extConf.strokeWeight,
                opacity: extConf.strokeOpacity
            };

            if (typeof config.pois !== 'undefined') {
                for (var i = 0; i < config.pois.length; i++) {
                    coordinatesArray.push([config.pois[i].latitude, config.pois[i].longitude]);
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
                insertRouteToDb(route.getLatLngs());
            });
            map.on("editable:vertex:deleted", function(event) {
                insertRouteToDb(route.getLatLngs());
            });
            map.on("editable:vertex:dragend", function(event) {
                insertRouteToDb(route.getLatLngs());
            });
        };

        /**
         * Create Radius
         */
        var createRadius = function() {
            marker = L.circle(
                [config.latitude, config.longitude],
                {
                    color: extConf.strokeColor,
                    opacity: extConf.strokeOpacity,
                    weight: extConf.strokeWeight,
                    fillColor: extConf.fillColor,
                    fillOpacity: extConf.fillOpacity,
                    radius: config.radius ? config.radius : extConf.defaultRadius
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
        var setLatLngFields = function(lat, lng, rad, address) {
            setFieldValue("latitude", lat);
            setFieldValue("longitude", lng);
            TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "latitude", createFieldName("latitude", false));
            TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "longitude", createFieldName("longitude", false));

            if (typeof rad !== "undefined" && rad > 0) {
                setFieldValue("radius", parseInt(rad));
                TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "radius", createFieldName("radius", false));
            }

            if (typeof address !== "undefined") {
                setFieldValue("address", address);
                TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "address", createFieldName("address", false));
            }
        };

        /**
         * Generate an uri to save all coordinates
         *
         * @param coordinates
         */
        var getUriForCoordinates = function(coordinates) {
            var routeObject = {};
            for (var index = 0; index < coordinates.length; index++) {
                routeObject[index] = coordinates[index]['lat'] + ',' + coordinates[index]['lng'];
            }
            return routeObject;
        };

        /**
         * Create field value
         *
         * @param field
         * @param hiddenRecord
         * @returns {string}
         */
        var createFieldName = function(field, hiddenRecord) {
            if (hiddenRecord === true) {
                return 'data[tx_maps2_domain_model_poicollection][' + config.uid + '][' + field + ']_hr';
            }
            return 'data[tx_maps2_domain_model_poicollection][' + config.uid + '][' + field + ']';
        };

        /**
         * Set field value
         *
         * @param field
         * @param value
         */
        var setFieldValue = function(field, value) {
            var fieldName = createFieldName(field, true);
            // set the old (< TYPO3 7.5) hidden record fields "*_hr"
            if (typeof document[TBE_EDITOR.formname][fieldName] !== 'undefined') {
                document[TBE_EDITOR.formname][fieldName].value = value;
            }
            // set the new (>= TYPO3 7.5) data fields "data-formengine-input-name"
            fieldName = createFieldName(field, false);
            var $humanReadableField = $('[data-formengine-input-name="' + fieldName + '"]');
            if ($humanReadableField.length) {
                $humanReadableField.val(value);
            }
            // set the form field which contains the data, which will be send by POST
            document[TBE_EDITOR.formname][fieldName].value = value;
        };

        /**
         * Save coordinated to DB
         *
         * @param coordinates
         */
        var insertRouteToDb = function(coordinates) {
            $.ajax({
                type: "POST",
                url: TYPO3.settings.ajaxUrls["maps2Ajax"],
                data: {
                    tx_maps2_maps2: {
                        objectName: "InsertRoute",
                        hash: config.hash,
                        arguments: {
                            uid: config.uid,
                            route: getUriForCoordinates(coordinates)
                        }
                    }
                }
            });
        };

        /**
         * read address, send it to Google and move map/marker to new location
         */
        var findAddress = function() {
            var $pacSearch = $(document.getElementById("pac-search"));

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
                                var lat = parseFloat(data[0].lat).toFixed(6);
                                var lng = parseFloat(data[0].lon).toFixed(6);
                                var address = data[0].address;
                                var formattedAddress = getFormattedAddress(address);

                                switch (config.collectionType) {
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
        var getFormattedAddress = function (address) {
            var formattedAddress = '';
            var city = '';

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

        switch (config.collectionType) {
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

        if (config.latitude && config.longitude) {
            map.panTo([config.latitude, config.longitude]);
        } else {
            // Fallback
            map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
        }

        // if maps2 was inserted in (bootstrap) tabs, we have to re-render the map
        $("ul.t3js-tabs a[data-toggle='tab']:eq(1)").on("shown.bs.tab", function() {
            map.invalidateSize();
            if (config.latitude && config.longitude) {
                map.panTo([config.latitude, config.longitude]);
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
            $element.data("config"),
            $element.data("extconf")
        );
    };
});
