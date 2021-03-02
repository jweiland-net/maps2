let $maps2OpenStreetMaps = [];

/**
 * Initialize Open Street Map
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
function OpenStreetMaps2($element, environment) {
    this.categorizedMarkers = {};
    this.pointMarkers = [];
    this.bounds = new L.LatLngBounds();
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
            this.map.fitBounds(this.bounds);
        }
    } else {
        this.createPointByCollectionType(environment);
        if (this.countObjectProperties(this.categorizedMarkers) > 1) {
            this.showSwitchableCategories(environment);
        }
        if (this.poiCollections.length > 1 && environment.settings.forceZoom === false) {
            this.map.fitBounds(this.bounds);
        } else {
            this.map.panTo([this.poiCollections[0].latitude, this.poiCollections[0].longitude]);
        }
    }
}

/**
 * Create Map
 *
 * @param environment
 */
OpenStreetMaps2.prototype.createMap = function (environment) {
    this.map = map = L.map(
        this.$element.get(0), {
            center: [environment.extConf.defaultLatitude, environment.extConf.defaultLongitude],
            zoom: environment.settings.zoom ? environment.settings.zoom : 12,
            editable: this.editable,
            scrollWheelZoom: environment.settings.activateScrollWheel !== '0'
        }
    );

    L.tileLayer(environment.settings.mapTile, {
        attribution: environment.settings.mapTileAttribution,
        maxZoom: 20
    }).addTo(this.map);
};

/**
 * Group Categories
 *
 * @param environment
 */
OpenStreetMaps2.prototype.groupCategories = function (environment) {
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
OpenStreetMaps2.prototype.showSwitchableCategories = function (environment) {
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
                if (isChecked) {
                    markers[categoryUid][i].setOpacity(1);
                } else {
                    markers[categoryUid][i].setOpacity(0);
                }
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
OpenStreetMaps2.prototype.getCheckbox = function (category) {
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
OpenStreetMaps2.prototype.countObjectProperties = function (obj) {
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
OpenStreetMaps2.prototype.createPointByCollectionType = function (environment) {
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
OpenStreetMaps2.prototype.createMarker = function (poiCollection, environment) {
    let categoryUid = "0";
    let marker = L.marker(
        [poiCollection.latitude, poiCollection.longitude],
        {
            'draggable': this.editable
        }
    ).addTo(this.map);

    for (let i = 0; i < poiCollection.categories.length; i++) {
        categoryUid = poiCollection.categories[i].uid;
        if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
            this.categorizedMarkers[categoryUid] = [];
        }
        this.categorizedMarkers[categoryUid].push(marker);
        this.pointMarkers.push(marker);
    }

    // assign first found marker icon, if available
    if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
        let icon = L.icon({
            iconUrl: poiCollection.markerIcon,
            iconSize: [poiCollection.markerIconWidth, poiCollection.markerIconHeight],
            iconAnchor: [poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY]
        });
        marker.setIcon(icon);
    }

    this.bounds.extend(marker.getLatLng());

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
OpenStreetMaps2.prototype.createArea = function (poiCollection, environment) {
    let latlngs = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
        let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
        this.bounds.extend(latLng);
        latlngs.push(latLng);
    }

    let marker = L.polygon(latlngs, {
        color: poiCollection.strokeColor,
        opacity: poiCollection.strokeOpacity,
        width: poiCollection.strokeWeight,
        fillColor: poiCollection.fillColor,
        fillOpacity: poiCollection.fillOpacity,
        radius: poiCollection.radius
    }).addTo(this.map);
    this.addInfoWindow(marker, poiCollection, environment);
};

/**
 * Create Route
 *
 * @param poiCollection
 * @param environment
 */
OpenStreetMaps2.prototype.createRoute = function (poiCollection, environment) {
    let latlngs = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
        let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
        this.bounds.extend(latLng);
        latlngs.push(latLng);
    }

    let marker = L.polyline(latlngs, {
        color: poiCollection.strokeColor,
        opacity: poiCollection.strokeOpacity,
        width: poiCollection.strokeWeight,
        fillColor: poiCollection.fillColor,
        fillOpacity: poiCollection.fillOpacity,
        radius: poiCollection.radius
    }).addTo(this.map);
    this.addInfoWindow(marker, poiCollection, environment);
};

/**
 * Create Radius
 *
 * @param poiCollection
 * @param environment
 */
OpenStreetMaps2.prototype.createRadius = function (poiCollection, environment) {
    let marker = L.circle([poiCollection.latitude, poiCollection.longitude], {
        color: poiCollection.strokeColor,
        opacity: poiCollection.strokeOpacity,
        width: poiCollection.strokeWeight,
        fillColor: poiCollection.fillColor,
        fillOpacity: poiCollection.fillOpacity,
        radius: poiCollection.radius
    }).addTo(this.map);

    this.bounds.extend(marker.getBounds());
    this.addInfoWindow(marker, poiCollection, environment);
};

/**
 * Add Info Window to element
 *
 * @param element
 * @param poiCollection
 * @param environment
 */
OpenStreetMaps2.prototype.addInfoWindow = function (element, poiCollection, environment) {
    element.on("click", function () {
        jQuery.ajax({
            url: window.location.protocol + "//" + window.location.host + "/index.php?id=" + environment.id + "&type=1614075471&tx_maps2_maps2[controller]=Ajax&tx_maps2_maps2[action]=process&tx_maps2_maps2[method]=renderInfoWindowContent",
            method: "POST",
            dataType: "json",
            data: {
                storagePids: environment.contentRecord.pages,
                poiCollection: poiCollection.uid
            }
        }).done(function(data) {
            element.bindPopup(data.content).openPopup();
        });
    });
}

/**
 * Create Marker with InfoWindow
 *
 * @param latitude
 * @param longitude
 */
OpenStreetMaps2.prototype.createMarkerByLatLng = function (latitude, longitude) {
    let marker = L.marker(
        [latitude, longitude]
    ).addTo(this.map);

    this.bounds.extend(marker.getLatLng());
};

/**
 * Check for item in list
 * Check if an item exists in a comma-separated list of items.
 *
 * @param list
 * @param item
 */
OpenStreetMaps2.prototype.inList = function (list, item) {
    let catSearch = ',' + list + ',';
    item = ',' + item + ',';
    return catSearch.search(item);
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
OpenStreetMaps2.prototype.addEditListeners = function ($mapContainer, marker, poiCollection, environment) {
    // update fields and marker while dragging
    marker.on('dragend', function() {
        let lat = marker.getLatLng().lat.toFixed(6);
        let lng = marker.getLatLng().lng.toFixed(6);
        $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(lat);
        $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(lng);
    });

    // update fields and marker when clicking on the map
    map.on('click', function(event) {
        marker.setLatLng(event.latlng);
        $mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(event.latlng.lat.toFixed(6));
        $mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(event.latlng.lng.toFixed(6));
    });
};

jQuery(".maps2").each(function () {
    let $element = jQuery(this);
    // override environment with settings of override
    let environment = $element.data("environment");
    let override = $element.data("override");
    environment = jQuery.extend(true, environment, override);
    $maps2OpenStreetMaps.push(new OpenStreetMaps2($element, environment));
});
