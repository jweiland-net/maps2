var $maps2OpenStreetMaps = [];

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

    this.createMap(environment);
    this.createPointByCollectionType(environment);

    if (this.countObjectProperties(this.categorizedMarkers) > 1) {
        this.showSwitchableCategories(environment);
    }
    if (this.poiCollections.length > 1) {
        this.map.fitBounds(this.bounds);
    }
}

/**
 * Create Map
 *
 * @param environment
 */
OpenStreetMaps2.prototype.createMap = function (environment) {
    this.map = map = L.map(
        this.$element.get(0)
    ).setView(
        [
            this.poiCollections[0].latitude,
            this.poiCollections[0].longitude
        ],
        parseInt(environment.settings.zoom)
    );

    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox.streets'
    }).addTo(this.map);
};

/**
 * Group Categories
 *
 * @param environment
 */
OpenStreetMaps2.prototype.groupCategories = function (environment) {
    var groupedCategories = {};
    var categoryUid = "0";
    for (var x = 0; x < this.poiCollections.length; x++) {
        for (var y = 0; y < this.poiCollections[x].categories.length; y++) {
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
    var categories = this.groupCategories(environment);
    var $form = jQuery("<form>")
        .addClass("txMaps2Form")
        .attr("id", "txMaps2Form-" + environment.contentRecord.uid);

    // Add checkbox for category
    for (var categoryUid in categories) {
        if (categories.hasOwnProperty(categoryUid)) {
            $form.append(this.getCheckbox(categories[categoryUid]));
            $form.find("#checkCategory_" + categoryUid).after(jQuery("<span />")
                .addClass("map-category")
                .text(categories[categoryUid].title));
        }
    }
    // create form
    var markers = this.categorizedMarkers;
    $form.find("input").on("click", function () {
        var isChecked = jQuery(this).is(":checked");
        var categoryUid = jQuery(this).val();
        if (markers.hasOwnProperty(categoryUid)) {
            for (var i = 0; i < markers[categoryUid].length; i++) {
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
    var count = 0;
    for (var key in obj) {
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
    for (var i = 0; i < this.poiCollections.length; i++) {
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
            /*case "Area":
             this.createArea(this.poiCollections[i], environment.extConf);
             break;
             case "Route":
             this.createRoute(this.poiCollections[i], environment.extConf);
             break;*/
            case "Radius":
                this.createRadius(this.poiCollections[i], environment.extConf);
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
    var categoryUid = "0";
    var marker = L.marker(
        [poiCollection.latitude, poiCollection.longitude]
    ).addTo(this.map);

    for (var i = 0; i < poiCollection.categories.length; i++) {
        categoryUid = poiCollection.categories[i].uid;
        if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
            this.categorizedMarkers[categoryUid] = [];
        }
        // assign first found marker icon, if available
        if (poiCollection.markerIcon !== "") {
            var icon = L.icon({
                iconUrl: poiCollection.markerIcon,
                iconSize: [poiCollection.markerIconWidth, poiCollection.markerIconHeight],
                iconAnchor: [poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY]
            });
            marker.setIcon(icon);
        }
        this.categorizedMarkers[categoryUid].push(marker);
        this.pointMarkers.push(marker);
    }
    this.bounds.extend(marker.getLatLng());
    marker.bindPopup(poiCollection.infoWindowContent);
};

/**
 * Create Radius
 *
 * @param poiCollection
 */
OpenStreetMaps2.prototype.createRadius = function (poiCollection) {
    var circle = L.circle(
        [poiCollection.latitude, poiCollection.longitude],
        {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            width: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity,
            radius: poiCollection.radius
        }
    ).addTo(this.map);

    this.bounds.extend(circle.getBounds());
};

/**
 * Check for item in list
 * Check if an item exists in a comma-separated list of items.
 *
 * @param list
 * @param item
 */
OpenStreetMaps2.prototype.inList = function (list, item) {
    var catSearch = ',' + list + ',';
    item = ',' + item + ',';
    return catSearch.search(item);
};

jQuery(".maps2").each(function () {
    var $element = jQuery(this);
    // override environment with settings of override
    var environment = $element.data("environment");
    var override = $element.data("override");
    environment = jQuery.extend(true, environment, override);
    $maps2OpenStreetMaps.push(new OpenStreetMaps2($element, environment));
});
