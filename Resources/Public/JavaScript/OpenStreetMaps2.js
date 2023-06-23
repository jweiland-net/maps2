/**
 * Initialize Open Street Map
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
function OpenStreetMap2($element, environment) {
    let me = this;

    me.allMarkers = [];
    me.categorizedMarkers = {};
    me.bounds = new L.LatLngBounds();
    me.$element = $element.css({
        height: environment.settings.mapHeight,
        width: environment.settings.mapWidth
    });
    me.poiCollections = me.$element.data("pois");
    me.editable = me.$element.hasClass("editMarker");

    /**
     * Create Map
     *
     * @param environment
     */
    me.createMap = function (environment) {
        me.map = map = L.map(
            me.$element.get(0), {
                center: [environment.extConf.defaultLatitude, environment.extConf.defaultLongitude],
                zoom: environment.settings.zoom ? environment.settings.zoom : 12,
                editable: me.editable,
                scrollWheelZoom: environment.settings.activateScrollWheel !== '0'
            }
        );

        L.tileLayer(environment.settings.mapTile, {
            attribution: environment.settings.mapTileAttribution,
            maxZoom: 20
        }).addTo(me.map);
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
                if (markerCategoryHasCheckboxWithStatus === isChecked) {
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
                if (isChecked) {
                    map.addLayer(markers[i]);
                } else {
                    map.removeLayer(markers[i]);
                }
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
                me.categorizedMarkers[categoryUid].push(marker);
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
        let marker = L.marker(
            [poiCollection.latitude, poiCollection.longitude],
            {
                'draggable': me.editable
            }
        ).addTo(me.map);

        // assign first found marker icon, if available
        if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
            let icon = L.icon({
                iconUrl: poiCollection.markerIcon,
                iconSize: [poiCollection.markerIconWidth, poiCollection.markerIconHeight],
                iconAnchor: [poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY]
            });
            marker.setIcon(icon);
        }

        me.bounds.extend(marker.getLatLng());

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
        let latlngs = [];
        for (let i = 0; i < poiCollection.pois.length; i++) {
            let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
            me.bounds.extend(latLng);
            latlngs.push(latLng);
        }

        let marker = L.polygon(latlngs, {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            width: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity,
            radius: poiCollection.radius
        }).addTo(me.map);

        me.addInfoWindow(marker, poiCollection, environment);

        return marker;
    };

    /**
     * Create Route
     *
     * @param poiCollection
     * @param environment
     */
    me.createRoute = function (poiCollection, environment) {
        let latlngs = [];
        for (let i = 0; i < poiCollection.pois.length; i++) {
            let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
            me.bounds.extend(latLng);
            latlngs.push(latLng);
        }

        let marker = L.polyline(latlngs, {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            width: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity,
            radius: poiCollection.radius
        }).addTo(me.map);

        me.addInfoWindow(marker, poiCollection, environment);

        return marker;
    };

    /**
     * Create Radius
     *
     * @param poiCollection
     * @param environment
     */
    me.createRadius = function (poiCollection, environment) {
        let marker = L.circle([poiCollection.latitude, poiCollection.longitude], {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            width: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity,
            radius: poiCollection.radius
        }).addTo(me.map);

        me.bounds.extend(marker.getBounds());

        me.addInfoWindow(marker, poiCollection, environment);

        return marker;
    };

    /**
     * Add Info Window to element
     *
     * @param element
     * @param poiCollection
     * @param environment
     */
    me.addInfoWindow = function (element, poiCollection, environment) {
        element.on("click", function () {
            jQuery.ajax({
                url: environment.ajaxUrl,
                method: "POST",
                dataType: "json",
                data: {
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
    me.createMarkerByLatLng = function (latitude, longitude) {
        let marker = L.marker(
            [latitude, longitude]
        ).addTo(me.map);

        me.bounds.extend(marker.getLatLng());
    };

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

    me.createMap(environment);

    if (typeof me.poiCollections === "undefined" || jQuery.isEmptyObject(me.poiCollections)) {
        // Plugin: CityMap
        let lat = me.$element.data("latitude");
        let lng = me.$element.data("longitude");
        if (lat && lng) {
            me.createMarkerByLatLng(lat, lng);
        }
    } else {
        me.createPointByCollectionType(environment);
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
            me.map.panTo([me.poiCollections[0].latitude, me.poiCollections[0].longitude]);
        }
    }
}

let $maps2OpenStreetMaps = [];

jQuery(".maps2").each(function () {
    let $element = jQuery(this);
    // override environment with settings of override
    let environment = $element.data("environment");
    let override = $element.data("override");
    environment = jQuery.extend(true, environment, override);
    $maps2OpenStreetMaps.push(new OpenStreetMap2($element, environment));
});
