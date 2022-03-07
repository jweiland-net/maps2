/**
 * Initialize Google Maps
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
function GoogleMaps2($element, environment) {
    let me = this;

    me.allMarkers = [];
    me.categorizedMarkers = {};
    me.pointMarkers = [];
    me.bounds = new google.maps.LatLngBounds();
    me.infoWindow = new google.maps.InfoWindow();
    me.$element = $element.css({
        height: environment.settings.mapHeight,
        width: environment.settings.mapWidth
    });
    me.poiCollections = me.$element.data("pois");
    me.editable = me.$element.hasClass("editMarker");

    /**
     * Create a MapOptions object which can be assigned to the Map object of Google
     *
     * @param settings
     * @constructor
     */
    me.MapOptions = function (settings) {
        this.mapTypeId = '';
        this.zoom = parseInt(settings.zoom);
        this.zoomControl = (parseInt(settings.zoomControl) !== 0);
        this.mapTypeControl = (parseInt(settings.mapTypeControl) !== 0);
        this.scaleControl = (parseInt(settings.scaleControl) !== 0);
        this.streetViewControl = (parseInt(settings.streetViewControl) !== 0);
        this.fullscreenControl = (parseInt(settings.fullScreenControl) !== 0);
        this.scrollwheel = settings.activateScrollWheel;
        this.styles = '';

        if (settings.styles) {
            this.styles = eval(settings.styles);
        }

        /**
         * @param mapTypeId
         */
        this.setMapTypeId = function (mapTypeId) {
            switch (mapTypeId) {
                case "google.maps.MapTypeId.HYBRID":
                case "hybrid":
                    this.mapTypeId = google.maps.MapTypeId.HYBRID;
                    break;
                case "google.maps.MapTypeId.ROADMAP":
                case "roadmap":
                    this.mapTypeId = google.maps.MapTypeId.ROADMAP;
                    break;
                case "google.maps.MapTypeId.SATELLITE":
                case "satellite":
                    this.mapTypeId = google.maps.MapTypeId.SATELLITE;
                    break;
                case "google.maps.MapTypeId.TERRAIN":
                case "terrain":
                    this.mapTypeId = google.maps.MapTypeId.TERRAIN;
                    break;
            }
        }

        this.setMapTypeId(settings.mapTypeId);
    }

    /**
     * Create CircleOptions which can be assigned to the Circle object of Google
     *
     * @param map
     * @param centerPosition
     * @param poiCollection
     * @constructor
     */
    me.CircleOptions = function (map, centerPosition, poiCollection) {
        this.map = map;
        this.center = centerPosition;
        this.radius = poiCollection.radius;
        this.strokeColor = poiCollection.strokeColor;
        this.strokeOpacity = poiCollection.strokeOpacity;
        this.strokeWeight = poiCollection.strokeWeight;
        this.fillColor = poiCollection.fillColor;
        this.fillOpacity = poiCollection.fillOpacity;
    }

    /**
     * Create PolygonOptions which can be assigned to the Polygon object of Google
     *
     * @param paths
     * @param poiCollection
     * @constructor
     */
    me.PolygonOptions = function(paths, poiCollection) {
        this.paths = paths;
        this.strokeColor = poiCollection.strokeColor;
        this.strokeOpacity = poiCollection.strokeOpacity;
        this.strokeWeight = poiCollection.strokeWeight;
        this.fillColor = poiCollection.fillColor;
        this.fillOpacity = poiCollection.fillOpacity;
    }

    /**
     * Create PolylineOptions which can be assigned to the Polyline object of Google
     *
     * @param paths
     * @param poiCollection
     * @constructor
     */
    me.PolylineOptions = function(paths, poiCollection) {
        this.path = paths;
        this.strokeColor = poiCollection.strokeColor;
        this.strokeOpacity = poiCollection.strokeOpacity;
        this.strokeWeight = poiCollection.strokeWeight;
    }

    /**
     * Create Map
     *
     * @param environment
     */
    me.createMap = function (environment) {
        me.map = new google.maps.Map(
            me.$element.get(0),
            new me.MapOptions(environment.settings)
        );
    };

    /**
     * Group Categories
     *
     * @param environment
     */
    me.groupCategories = function (environment) {
        let groupedCategories = {};
        let categoryUid = "0";
        for (let x = 0; x < me.poiCollections.length; x++) {
            for (let y = 0; y < me.poiCollections[x].categories.length; y++) {
                categoryUid = String(me.poiCollections[x].categories[y].uid);
                if (me.inList(environment.settings.categories, categoryUid) > -1 && !groupedCategories.hasOwnProperty(categoryUid)) {
                    groupedCategories[categoryUid] = me.poiCollections[x].categories[y];
                }
            }
        }

        return groupedCategories;
    };

    /**
     * Get categories of all checkboxes with a given status
     *
     * @param $form The HTML form element containing the checkboxes
     * @param isChecked Get checkboxes of this status only
     */
    me.getCategoriesOfCheckboxesWithStatus = function ($form, isChecked) {
        let categories = [];
        let $checkboxes = isChecked ? $form.find("input:checked") : $form.find("input:not(input:checked)");
        $checkboxes.each(function () {
            categories.push(parseInt($(this).val()));
        });

        return categories;
    }

    me.getMarkersToChangeVisibilityFor = function (categoryUid, $form, isChecked) {
        let markers = [];
        if (me.allMarkers.length === 0) {
            return markers;
        }

        let marker = null;
        let allCategoriesOfMarker = null;
        let categoriesOfCheckboxesWithStatus = me.getCategoriesOfCheckboxesWithStatus($form, isChecked);
        for (let i = 0; i < me.allMarkers.length; i++) {
            marker = me.allMarkers[i];
            allCategoriesOfMarker = marker.poiCollection.categories;
            if (allCategoriesOfMarker.length === 0) {
                continue;
            }

            let markerCategoryHasCheckboxWithStatus;
            for (let j = 0; j < allCategoriesOfMarker.length; j++) {
                markerCategoryHasCheckboxWithStatus = false;
                for (let k = 0; k < categoriesOfCheckboxesWithStatus.length; k++) {
                    if (allCategoriesOfMarker[j].uid === categoriesOfCheckboxesWithStatus[k]) {
                        markerCategoryHasCheckboxWithStatus = true;
                    }
                }
                if (markerCategoryHasCheckboxWithStatus === false) {
                    break;
                }
            }

            if (markerCategoryHasCheckboxWithStatus) {
                markers.push(marker.marker);
            }
        }

        return markers;
    }

    /**
     * Show switchable categories
     *
     * @param environment
     */
    me.showSwitchableCategories = function (environment) {
        let categories = me.groupCategories(environment);
        let $form = jQuery("<form>")
            .addClass("txMaps2Form")
            .attr("id", "txMaps2Form-" + environment.contentRecord.uid);

        // Add checkbox for category
        for (let categoryUid in categories) {
            if (categories.hasOwnProperty(categoryUid)) {
                $form.append(me.getCheckbox(categories[categoryUid]));
                $form.find("#checkCategory_" + categoryUid).after(jQuery("<span />")
                    .addClass("map-category")
                    .text(categories[categoryUid].title));
            }
        }

        // Add listener for checkboxes
        $form.find("input").on("click", function () {
            let $checkbox = jQuery(this);
            let isChecked = $checkbox.is(":checked");
            let categoryUid = $checkbox.val();
            let markers = me.getMarkersToChangeVisibilityFor(categoryUid, $form, isChecked);

            for (let i = 0; i < markers.length; i++) {
                markers[i].setVisible(isChecked);
            }
        });

        me.$element.after($form);
    };

    /**
     * Get Checkbox for Category
     *
     * @param category
     */
    me.getCheckbox = function (category) {
        return jQuery("<div />")
            .addClass("form-group").append(
                jQuery("<div />")
                    .addClass("checkbox").append(
                    jQuery("<label />").append(
                        jQuery("<input />")
                            .attr({
                                type: "checkbox",
                                class: "checkCategory",
                                id: "checkCategory_" + category.uid,
                                checked: "checked",
                                value: category.uid
                            })
                    )
                )
            );
    };

    /**
     * Count Object properties
     *
     * @param obj
     */
    me.countObjectProperties = function (obj) {
        let count = 0;
        for (let key in obj) {
            if (obj.hasOwnProperty(key)) {
                count++;
            }
        }
        return count;
    };

    /**
     * Create Point by CollectionType
     *
     * @param environment
     */
    me.createPointByCollectionType = function (environment) {
        let marker;
        let categoryUid = 0;

        for (let i = 0; i < me.poiCollections.length; i++) {
            if (me.poiCollections[i].strokeColor === "") {
                me.poiCollections[i].strokeColor = environment.extConf.strokeColor;
            }
            if (me.poiCollections[i].strokeOpacity === "") {
                me.poiCollections[i].strokeOpacity = environment.extConf.strokeOpacity;
            }
            if (me.poiCollections[i].strokeWeight === "") {
                me.poiCollections[i].strokeWeight = environment.extConf.strokeWeight;
            }
            if (me.poiCollections[i].fillColor === "") {
                me.poiCollections[i].fillColor = environment.extConf.fillColor;
            }
            if (me.poiCollections[i].fillOpacity === "") {
                me.poiCollections[i].fillOpacity = environment.extConf.fillOpacity;
            }

            marker = null;
            switch (me.poiCollections[i].collectionType) {
                case "Point":
                    marker = me.createMarker(me.poiCollections[i], environment);
                    break;
                case "Area":
                    marker = me.createArea(me.poiCollections[i], environment);
                    break;
                case "Route":
                    marker = me.createRoute(me.poiCollections[i], environment);
                    break;
                case "Radius":
                    marker = me.createRadius(me.poiCollections[i], environment);
                    break;
            }

            if (marker === null) {
                continue;
            }

            me.allMarkers.push({
                marker: marker,
                poiCollection: me.poiCollections[i]
            });

            categoryUid = 0;
            for (let c = 0; c < me.poiCollections[i].categories.length; c++) {
                categoryUid = me.poiCollections[i].categories[c].uid;
                if (!me.categorizedMarkers.hasOwnProperty(categoryUid)) {
                    me.categorizedMarkers[categoryUid] = [];
                }
                me.categorizedMarkers[categoryUid].push({
                    marker: marker,
                    relatedCategories: me.poiCollections[i].categories
                });
            }
        }
    };

    /**
     * Create Marker with InfoWindow
     *
     * @param poiCollection
     * @param environment
     */
    me.createMarker = function (poiCollection, environment) {
        let categoryUid = "0";
        let marker = new google.maps.Marker({
            position: new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
            map: me.map
        });
        marker.setDraggable(me.editable);

        // assign first found marker icon, if available
        if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
            let icon = {
                url: poiCollection.markerIcon,
                scaledSize: new google.maps.Size(poiCollection.markerIconWidth, poiCollection.markerIconHeight),
                anchor: new google.maps.Point(poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY)
            };
            marker.setIcon(icon);
        }

        me.pointMarkers.push(marker);
        me.bounds.extend(marker.position);

        if (me.editable) {
            me.addEditListeners(me.$element, marker, poiCollection, environment);
        } else {
            me.addInfoWindow(marker, poiCollection, environment);
        }

        return marker;
    };

    /**
     * Create Area
     *
     * @param poiCollection
     * @param environment
     */
    me.createArea = function (poiCollection, environment) {
        let latLng;
        let paths = [];
        for (let i = 0; i < poiCollection.pois.length; i++) {
            latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
            me.bounds.extend(latLng);
            paths.push(latLng);
        }

        if (paths.length === 0) {
            paths.push(me.mapPosition);
        }

        let area = new google.maps.Polygon(new me.PolygonOptions(paths, poiCollection));
        area.setMap(me.map);
        me.addInfoWindow(area, poiCollection, environment);

        return area;
    };

    /**
     * Create Route
     *
     * @param poiCollection
     * @param environment
     */
    me.createRoute = function (poiCollection, environment) {
        let latLng;
        let paths = [];
        for (let i = 0; i < poiCollection.pois.length; i++) {
            latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
            me.bounds.extend(latLng);
            paths.push(latLng);
        }

        if (paths.length === 0) {
            paths.push(me.mapPosition);
        }

        let route = new google.maps.Polyline(new me.PolylineOptions(paths, poiCollection));
        route.setMap(me.map);
        me.addInfoWindow(route, poiCollection, environment);

        return route;
    };

    /**
     * Create Radius
     *
     * @param poiCollection
     * @param environment
     */
    me.createRadius = function (poiCollection, environment) {
        let circle = new google.maps.Circle(
            new me.CircleOptions(
                me.map,
                new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
                poiCollection
            )
        );

        me.bounds.union(circle.getBounds());
        me.addInfoWindow(circle, poiCollection, environment);

        return circle;
    };

    /**
     * Add Info Window to element
     *
     * @param element
     * @param poiCollection
     * @param environment
     */
    me.addInfoWindow = function (element, poiCollection, environment) {
        // we need these both vars to be set global. So that we can access them in Listener
        let infoWindow = me.infoWindow;
        let map = me.map;
        google.maps.event.addListener(element, "click", function (event) {
            let url = window.location.protocol + "//" + window.location.host;
            url += "/index.php?id=" + environment.id + "&type=1614075471";
            if (poiCollection.sysLanguageUid) {
                url += "&L=" + poiCollection.sysLanguageUid;
            }
            url += "&tx_maps2_maps2[controller]=Ajax&tx_maps2_maps2[action]=process&tx_maps2_maps2[method]=renderInfoWindowContent"
            jQuery.ajax({
                url: url,
                method: "POST",
                dataType: "json",
                data: {
                    storagePids: environment.contentRecord.pages,
                    poiCollection: poiCollection.uid
                }
            }).done(function(data) {
                infoWindow.close();
                infoWindow.setContent(data.content);

                // Do not set pointer of InfoWindow to the same pointer of the POI icon.
                // In case of Point the pointer of InfoWindow should be at mouse position.
                if (poiCollection.collectionType === "Point") {
                    infoWindow.setPosition(null);
                    infoWindow.open(map, element);
                } else {
                    infoWindow.setPosition(new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude));
                    infoWindow.open(map);
                }
            })
        });
    }

    /**
     * Check for item in list
     * Check if an item exists in a comma-separated list of items.
     *
     * @param list
     * @param item
     */
    me.inList = function (list, item) {
        let catSearch = ',' + list + ',';
        item = ',' + item + ',';
        return catSearch.search(item);
    };

    /**
     * Create Marker with InfoWindow
     *
     * @param latitude
     * @param longitude
     */
    me.createMarkerByLatLng = function (latitude, longitude) {
        let marker = new google.maps.Marker({
            position: new google.maps.LatLng(latitude, longitude),
            map: me.map
        });
        me.bounds.extend(marker.position);
    };

    /**
     * Add Edit Listeners
     * This will only work for Markers (Point)
     *
     * @param $mapContainer
     * @param marker
     * @param poiCollection
     * @param environment
     */
    me.addEditListeners = function ($mapContainer, marker, poiCollection, environment) {
        // update fields and marker while dragging
        google.maps.event.addListener(marker, 'dragend', function () {
            let lat = marker.getPosition().lat().toFixed(6);
            let lng = marker.getPosition().lng().toFixed(6);
            $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(lat);
            $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(lng);
        });

        // update fields and marker when clicking on the map
        google.maps.event.addListener(me.map, 'click', function (event) {
            marker.setPosition(event.latLng);
            $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(event.latLng.lat().toFixed(6));
            $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(event.latLng.lng().toFixed(6));
        });
    };

    me.createMap(environment);

    if (typeof me.poiCollections === "undefined" || jQuery.isEmptyObject(me.poiCollections)) {
        // Plugin: CityMap
        let lat = me.$element.data("latitude");
        let lng = me.$element.data("longitude");
        if (lat && lng) {
            me.createMarkerByLatLng(lat, lng);
            me.map.setCenter(new google.maps.LatLng(lat, lng));
        } else {
            // Fallback
            me.map.setCenter(new google.maps.LatLng(environment.extConf.defaultLatitude, environment.extConf.defaultLongitude));
        }
    } else {
        // normal case
        me.createPointByCollectionType(environment);
        if (
            typeof environment.settings.markerClusterer !== 'undefined'
            && environment.settings.markerClusterer.enable === 1
        ) {
            new me.MarkerClusterer(
                me.map,
                me.pointMarkers,
                {imagePath: environment.settings.markerClusterer.imagePath}
            );
        }
        if (me.countObjectProperties(me.categorizedMarkers) > 1) {
            me.showSwitchableCategories(environment);
        }
        if (
            environment.settings.forceZoom === false
            && (
                me.poiCollections.length > 1
                || (
                    me.poiCollections.length === 1
                    && (
                        me.poiCollections[0].collectionType === "Area"
                        || me.poiCollections[0].collectionType === "Route"
                    )
                )
            )
        ) {
            me.map.fitBounds(me.bounds);
        } else {
            me.map.setCenter(new google.maps.LatLng(me.poiCollections[0].latitude, me.poiCollections[0].longitude));
        }
    }
}

let $maps2GoogleMaps = [];

/**
 * This function will be called by the &callback argument of the Google Maps API library
 */
function initMap() {
    jQuery(".maps2").each(function () {
        let $element = jQuery(this);

        // override environment with settings of override
        let environment = $element.data("environment");
        let override = $element.data("override");
        environment = jQuery.extend(true, environment, override);
        $maps2GoogleMaps.push(new GoogleMaps2($element, environment));
    });

    // initialize radius search
    let $address = jQuery("#maps2Address");
    let $radius = jQuery("#maps2Radius");
    if ($address.length && $radius.length) {
        let input = document.getElementById("maps2Address");
        let autocomplete = new google.maps.places.Autocomplete(input, {fields: ['place_id']});
        $(input).keydown(function (e) {
            if (e.which === 13) return false;
        });
    }
}
