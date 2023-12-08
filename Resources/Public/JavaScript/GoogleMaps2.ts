/**
 * Initialize Google Maps
 *
 * @param $element
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @constructor
 */
class GoogleMaps2 {
    public allMarkers: any[];
    public categorizedMarkers: any;
    public pointMarkers: any[];
    public bounds: any;
    public infoWindow: any;
    public poiCollections: any;
    public editable: any;
    public map: any;

    constructor(public element: HTMLElement, environment: any) {
        let me = this;

        me.allMarkers = [];
        me.categorizedMarkers = {};
        me.pointMarkers = [];
        me.bounds = new google.maps.LatLngBounds();
        me.infoWindow = new google.maps.InfoWindow();
        me.element.style.height = String(environment.settings.mapHeight);
        me.element.style.width = String(environment.settings.mapWidth);
        me.poiCollections = JSON.parse(me.element.getAttribute("data-pois"));
        me.editable = me.element.classList.contains("editMarker");
        me.createMap(environment);

        if (typeof me.poiCollections === "undefined" || jQuery.isEmptyObject(me.poiCollections)) {
            // Plugin: CityMap
            let lat: Number = Number(me.element.getAttribute("data-latitude"));
            let lng: Number = Number(me.element.getAttribute("data-longitude"));
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
                new MarkerClusterer(
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

    /**
     * Create a MapOptions object which can be assigned to the Map object of Google
     *
     * @param settings
     * @constructor
     */
    MapOptions(settings: any) {
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

        this.setMapTypeId = function (mapTypeId: any) {
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
    CircleOptions(map: any, centerPosition: any, poiCollection: any) {
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
    PolygonOptions(paths: any, poiCollection: any) {
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
    PolylineOptions(paths: any, poiCollection: any) {
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
    createMap(environment: any): void {
        this.map = new google.maps.Map(
            this.element,
            new this.MapOptions(environment.settings)
        );
    };

    /*
        And so on, you can create the methods inside the class GoogleMaps2 as we did above for these methods:
        groupCategories, getCategoriesOfCheckboxesWithStatus, getMarkersToChangeVisibilityFor, showSwitchableCategories,
        getCheckbox, countObjectProperties, createPointByCollectionType, createMarker, createArea, createRoute,
        createRadius, addInfoWindow, inList, createMarkerByLatLng and addEditListeners
    */

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
