let $maps2GoogleMaps = [];

/**
 * Create a MapOptions object which can be assigned to the Map object of Google
 *
 * @param settings
 * @constructor
 */
function MapOptions(settings) {
    this.zoom = parseInt(settings.zoom);
    this.zoomControl = (parseInt(settings.zoomControl) !== 0);
    this.mapTypeControl = (parseInt(settings.mapTypeControl) !== 0);
    this.scaleControl = (parseInt(settings.scaleControl) !== 0);
    this.streetViewControl = (parseInt(settings.streetViewControl) !== 0);
    this.fullscreenControl = (parseInt(settings.fullScreenControl) !== 0);
    this.scrollwheel = settings.activateScrollWheel;
    if (settings.styles) {
        this.styles = eval(settings.styles);
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
function CircleOptions(map, centerPosition, poiCollection) {
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
function PolygonOptions(paths, poiCollection) {
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
function PolylineOptions(paths, poiCollection) {
    this.path = paths;
    this.strokeColor = poiCollection.strokeColor;
    this.strokeOpacity = poiCollection.strokeOpacity;
    this.strokeWeight = poiCollection.strokeWeight;
}

/**
 * Instead of using eval() I decided to create a switch construct
 * which is more save
 *
 * @param mapTypeId
 */
MapOptions.prototype.setMapTypeId = function (mapTypeId) {
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
};

/**
 * Initialize Google Maps
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
function GoogleMaps2($element, environment) {
    this.categorizedMarkers = {};
    this.pointMarkers = [];
    this.bounds = new google.maps.LatLngBounds();
    this.infoWindow = new google.maps.InfoWindow();
    this.$element = $element.css({
        height: environment.settings.mapHeight,
        width: environment.settings.mapWidth
    });
    this.poiCollections = this.$element.data("pois");
    this.editable = this.$element.hasClass("editMarker");

    this.createMap(environment);

    if (typeof this.poiCollections === "undefined" || jQuery.isEmptyObject(this.poiCollections)) {
        // Plugin: CityMap
        let lat = this.$element.data("latitude");
        let lng = this.$element.data("longitude");
        if (lat && lng) {
            this.createMarkerByLatLng(lat, lng);
            this.map.setCenter(new google.maps.LatLng(lat, lng));
            this.map.setZoom(15);
        } else {
            // Fallback
            this.map.setCenter(new google.maps.LatLng(environment.extConf.defaultLatitude, environment.extConf.defaultLongitude));
        }
    } else {
        // normal case
        this.createPointByCollectionType(environment);
        if (
            typeof environment.settings.markerClusterer !== 'undefined'
            && environment.settings.markerClusterer.enable === 1
        ) {
            new MarkerClusterer(
                this.map,
                this.pointMarkers,
                {imagePath: environment.settings.markerClusterer.imagePath}
            );
        }
        if (this.countObjectProperties(this.categorizedMarkers) > 1) {
            this.showSwitchableCategories(environment);
        }
        if (
            environment.settings.forceZoom === false
            && (
                this.poiCollections.length > 1
                || (
                    this.poiCollections.length === 1
                    && (
                        this.poiCollections[0].collectionType === "Area"
                        || this.poiCollections[0].collectionType === "Route"
                    )
                )
            )
        ) {
            this.map.fitBounds(this.bounds);
        } else {
            this.map.setCenter(new google.maps.LatLng(this.poiCollections[0].latitude, this.poiCollections[0].longitude));
        }
    }
}

/**
 * Create Map
 *
 * @param environment
 */
GoogleMaps2.prototype.createMap = function (environment) {
    this.map = new google.maps.Map(
        this.$element.get(0),
        new MapOptions(environment.settings)
    );
};

/**
 * Group Categories
 *
 * @param environment
 */
GoogleMaps2.prototype.groupCategories = function (environment) {
    let groupedCategories = {};
    let categoryUid = "0";
    for (let x = 0; x < this.poiCollections.length; x++) {
        for (let y = 0; y < this.poiCollections[x].categories.length; y++) {
            categoryUid = String(this.poiCollections[x].categories[y].uid);
            if (this.inList(environment.settings.categories, categoryUid) > -1 && !groupedCategories.hasOwnProperty(categoryUid)) {
                groupedCategories[categoryUid] = this.poiCollections[x].categories[y];
            }
        }
    }
    return groupedCategories;
};

/**
 * Show switchable categories
 *
 * @param environment
 */
GoogleMaps2.prototype.showSwitchableCategories = function (environment) {
    let categories = this.groupCategories(environment);
    let $form = jQuery("<form>")
        .addClass("txMaps2Form")
        .attr("id", "txMaps2Form-" + environment.contentRecord.uid);

    // Add checkbox for category
    for (let categoryUid in categories) {
        if (categories.hasOwnProperty(categoryUid)) {
            $form.append(this.getCheckbox(categories[categoryUid]));
            $form.find("#checkCategory_" + categoryUid).after(jQuery("<span />")
                .addClass("map-category")
                .text(categories[categoryUid].title));
        }
    }
    // create form
    let markers = this.categorizedMarkers;
    $form.find("input").on("click", function () {
        let isChecked = jQuery(this).is(":checked");
        let categoryUid = jQuery(this).val();
        if (markers.hasOwnProperty(categoryUid)) {
            for (let i = 0; i < markers[categoryUid].length; i++) {
                markers[categoryUid][i].setVisible(isChecked);
            }
        }
    });
    this.$element.after($form);
};

/**
 * Get Checkbox for Category
 *
 * @param category
 */
GoogleMaps2.prototype.getCheckbox = function (category) {
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
GoogleMaps2.prototype.countObjectProperties = function (obj) {
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
GoogleMaps2.prototype.createPointByCollectionType = function (environment) {
    for (let i = 0; i < this.poiCollections.length; i++) {
        if (this.poiCollections[i].strokeColor === "") {
            this.poiCollections[i].strokeColor = environment.extConf.strokeColor;
        }
        if (this.poiCollections[i].strokeOpacity === "") {
            this.poiCollections[i].strokeOpacity = environment.extConf.strokeOpacity;
        }
        if (this.poiCollections[i].strokeWeight === "") {
            this.poiCollections[i].strokeWeight = environment.extConf.strokeWeight;
        }
        if (this.poiCollections[i].fillColor === "") {
            this.poiCollections[i].fillColor = environment.extConf.fillColor;
        }
        if (this.poiCollections[i].fillOpacity === "") {
            this.poiCollections[i].fillOpacity = environment.extConf.fillOpacity;
        }
        switch (this.poiCollections[i].collectionType) {
            case "Point":
                this.createMarker(this.poiCollections[i], environment);
                break;
            case "Area":
                this.createArea(this.poiCollections[i], environment);
                break;
            case "Route":
                this.createRoute(this.poiCollections[i], environment);
                break;
            case "Radius":
                this.createRadius(this.poiCollections[i], environment);
                break;
        }
    }
};

/**
 * Create Marker with InfoWindow
 *
 * @param poiCollection
 * @param environment
 */
GoogleMaps2.prototype.createMarker = function (poiCollection, environment) {
    let categoryUid = "0";
    let marker = new google.maps.Marker({
        position: new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
        map: this.map
    });
    marker.setDraggable(this.editable);
    for (let i = 0; i < poiCollection.categories.length; i++) {
        categoryUid = poiCollection.categories[i].uid;
        if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
            this.categorizedMarkers[categoryUid] = [];
        }
        this.categorizedMarkers[categoryUid].push(marker);
    }

    // assign first found marker icon, if available
    if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
        let icon = {
            url: poiCollection.markerIcon,
            scaledSize: new google.maps.Size(poiCollection.markerIconWidth, poiCollection.markerIconHeight),
            anchor: new google.maps.Point(poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY)
        };
        marker.setIcon(icon);
    }

    this.pointMarkers.push(marker);
    this.bounds.extend(marker.position);

    if (this.editable) {
        this.addEditListeners(this.$element, marker, poiCollection, environment);
    } else {
        this.addInfoWindow(marker, poiCollection, environment);
    }
};

/**
 * Create Area
 *
 * @param poiCollection
 * @param environment
 */
GoogleMaps2.prototype.createArea = function (poiCollection, environment) {
    let latLng;
    let paths = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
        latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
        this.bounds.extend(latLng);
        paths.push(latLng);
    }

    if (paths.length === 0) {
        paths.push(this.mapPosition);
    } else {
        let area = new google.maps.Polygon(new PolygonOptions(paths, poiCollection));
        area.setMap(this.map);
        this.addInfoWindow(area, poiCollection, environment);
    }
};

/**
 * Create Route
 *
 * @param poiCollection
 * @param environment
 */
GoogleMaps2.prototype.createRoute = function (poiCollection, environment) {
    let latLng;
    let paths = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
        latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
        this.bounds.extend(latLng);
        paths.push(latLng);
    }

    if (paths.length === 0) {
        paths.push(this.mapPosition);
    } else {
        let route = new google.maps.Polyline(new PolylineOptions(paths, poiCollection));
        route.setMap(this.map);
        this.addInfoWindow(route, poiCollection, environment);
    }
};

/**
 * Create Radius
 *
 * @param poiCollection
 * @param environment
 */
GoogleMaps2.prototype.createRadius = function (poiCollection, environment) {
    let circle = new google.maps.Circle(
        new CircleOptions(
            this.map,
            new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
            poiCollection
        )
    );
    this.bounds.union(circle.getBounds());
    this.addInfoWindow(circle, poiCollection, environment);
};

/**
 * Add Info Window to element
 *
 * @param element
 * @param poiCollection
 * @param environment
 */
GoogleMaps2.prototype.addInfoWindow = function (element, poiCollection, environment) {
    // we need these both vars to be set global. So that we can access them in Listener
    let infoWindow = this.infoWindow;
    let map = this.map;
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
GoogleMaps2.prototype.inList = function (list, item) {
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
GoogleMaps2.prototype.createMarkerByLatLng = function (latitude, longitude) {
    let marker = new google.maps.Marker({
        position: new google.maps.LatLng(latitude, longitude),
        map: this.map
    });
    this.bounds.extend(marker.position);
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
GoogleMaps2.prototype.addEditListeners = function ($mapContainer, marker, poiCollection, environment) {
    // update fields and marker while dragging
    google.maps.event.addListener(marker, 'dragend', function () {
        let lat = marker.getPosition().lat().toFixed(6);
        let lng = marker.getPosition().lng().toFixed(6);
        $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(lat);
        $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(lng);
    });

    // update fields and marker when clicking on the map
    google.maps.event.addListener(this.map, 'click', function (event) {
        marker.setPosition(event.latLng);
        $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(event.latLng.lat().toFixed(6));
        $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(event.latLng.lng().toFixed(6));
    });
};

/**
 * This function will be called by the &callback argument of the Google Maps API library
 */
function initMap() {
    let $element;
    let environment;
    jQuery(".maps2").each(function () {
        $element = jQuery(this);
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
