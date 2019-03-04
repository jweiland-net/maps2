var $maps2OpenStreetMaps = [];

/**
 * Initialize Open Street Maps
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
function OpenStreetMaps2($element, environment) {
    this.pointMarkers = [];
    this.$element = $element.css({
        height: environment.settings.mapHeight,
        width: environment.settings.mapWidth
    });
    this.poiCollections = this.$element.data("pois");

    this.createMap(environment);

    this.createPointByCollectionType(environment);
}

/**
 * Create Map
 *
 * @param environment
 */
OpenStreetMaps2.prototype.createMap = function (environment) {
    this.map = map = L.map(this.$element.get(0)).setView([this.poiCollections[0].latitude, this.poiCollections[0].longitude], parseInt(environment.settings.zoom));

    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox.streets'
    }).addTo(this.map);
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
                break;
            case "Radius":
                this.createRadius(this.poiCollections[i], environment.extConf);
                break;*/
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
    var marker = L.marker(
        [poiCollection.latitude, poiCollection.longitude]
    ).addTo(this.map);
};

jQuery(".maps2").each(function () {
    var $element = jQuery(this);
    // override environment with settings of override
    var environment = $element.data("environment");
    var override = $element.data("override");
    environment = jQuery.extend(true, environment, override);
    $maps2OpenStreetMaps.push(new OpenStreetMaps2($element, environment));
});
