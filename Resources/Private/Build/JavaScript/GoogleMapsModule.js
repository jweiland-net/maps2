import { ExtConf, PoiCollection } from '@jweiland/maps2/Classes.js';
import FormEngine from "@typo3/backend/form-engine.js";
import Notification from"@typo3/backend/notification.js"

class GoogleMapsModule {
  selector = '#maps2ConfigurationMap';
  record = [];
  extConf = [];
  marker = {};
  map = {};
  infoWindow = {};
  infoWindowContent = {};

  constructor() {
    let googleMaps = document.querySelector(this.selector);
    let poiCollection = JSON.parse(googleMaps.dataset.poiCollection);
    let extConf = JSON.parse(googleMaps.dataset.extConf);

    this.load(extConf).then(() => {
      this.initialize(googleMaps, poiCollection, extConf);
    });
  }

  load = (extConf) => {
    window._GoogleMapsModule = this;
    window._GoogleMapsModule.initMaps = this.initMaps.bind(this);

    let promise = new Promise(resolve => {
      this.resolve = resolve;

      const script = document.createElement("script");
      script.src = extConf.googleMapsLibrary + "&callback=_GoogleMapsModule.initMaps";
      script.async = true;
      document.body.append(script);
    });

    return promise;
  }

  initMaps = () => {
    if (this.resolve) {
      this.resolve();
    }
  };

  /**
   * @param {HTMLElement} element
   * @param {PoiCollection} poiCollection
   * @param {ExtConf} extConf
   */
  initialize = (element, poiCollection, extConf) => {
    this.record = poiCollection;
    this.extConf = extConf;
    this.infoWindow = new google.maps.InfoWindow();
    this.infoWindowContent = document.getElementById("infowindow-content");
    this.map = this.createMap(element);

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

    switch (poiCollection.collectionType) {
      case "Point":
        this.createMarker(poiCollection);
        break;
      case "Area":
        this.createArea(poiCollection);
        break;
      case "Route":
        this.createRoute(poiCollection);
        break;
      case "Radius":
        this.createRadius(poiCollection);
        break;
    }

    this.findAddress();

    if (poiCollection.latitude && poiCollection.longitude) {
      this.map.setCenter(new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude));
    } else {
      // Fallback
      this.map.setCenter(new google.maps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
    }

    // if maps2 was inserted in (bootstrap) tabs, we have to re-render the map
    document.querySelector("ul.t3js-tabs li:nth-of-type(2) a[data-bs-toggle='tab']").addEventListener("shown.bs.tab", () => {
      google.maps.event.trigger(this.map, "resize");
      if (poiCollection.latitude && poiCollection.longitude) {
        this.map.setCenter(new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude));
      } else {
        this.map.setCenter(new google.maps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
      }
    });
  };

  createMapOptions = function() {
    return {
      zoom: 14,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
  };

  createCircleOptions = function(map, record, extConf) {
    let circleOptions = {
      map: map,
      center: new google.maps.LatLng(record.latitude, record.longitude),
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

  createPolygonOptions = function(paths, extConf) {
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

  createPolylineOptions = function(paths, extConf) {
    return {
      path: paths,
      strokeColor: extConf.strokeColor,
      strokeOpacity: extConf.strokeOpacity,
      strokeWeight: extConf.strokeWeight,
      editable: true
    };
  };

  createMap = function(element) {
    return new google.maps.Map(
      element,
      this.createMapOptions()
    );
  };

  createMarker = function(record) {
    this.marker = new google.maps.Marker({
      position: new google.maps.LatLng(record.latitude, record.longitude),
      map: this.map,
      draggable: true
    });

    this.infoWindow.setContent(this.infoWindowContent);

    // open InfoWindow, if marker was clicked.
    this.marker.addListener("click", function() {
      this.infoWindow.open(this.map, this.marker);
    });

    // update fields and marker while dragging
    google.maps.event.addListener(this.marker, 'dragend', function() {
      this.setLatLngFields(
        this.marker.getPosition().lat().toFixed(6),
        this.marker.getPosition().lng().toFixed(6),
        0
      );
    });

    // update fields and marker when clicking on the map
    google.maps.event.addListener(this.map, 'click', function(event) {
      this.marker.setPosition(event.latLng);
      this.setLatLngFields(
        event.latLng.lat().toFixed(6),
        event.latLng.lng().toFixed(6),
        0
      );
    });
  };

  createArea = function(record) {
    let coordinatesArray = [];

    if (record.configuration_map) {
      for (let i = 0; i < record.configuration_map.length; i++) {
        coordinatesArray.push(
          new google.maps.LatLng(
            record.configuration_map[i].latitude,
            record.configuration_map[i].longitude
          )
        );
      }
    }

    if (coordinatesArray.length === 0) {
      coordinatesArray.push(
        new google.maps.LatLng(
          record.latitude,
          record.longitude
        )
      );
    }

    let area = new google.maps.Polygon(
      this.createPolygonOptions(coordinatesArray, this.extConf)
    );
    let path = area.getPath();

    area.setMap(this.map);

    // Listener which will be called, if a vertex was moved to a new location
    google.maps.event.addListener(path, 'set_at', function() {
      this.storeRouteAsJson(area);
    });
    // Listener to add new vertex in between a route
    google.maps.event.addListener(path, 'insert_at', function() {
      this.storeRouteAsJson(area);
    });
    // Listener to remove a vertex
    google.maps.event.addListener(area, 'rightclick', function(event) {
      area.getPath().removeAt(event.vertex);
      this.storeRouteAsJson(area);
    });
    // Listener to add a new vertex. Will not be called, while inserting a vertex in between
    google.maps.event.addListener(this.map, 'click', function(event) {
      area.getPath().push(event.latLng);
    });
    // update fields for saving map position
    google.maps.event.addListener(this.map, 'dragend', function() {
      this.setLatLngFields(
        this.map.getCenter().lat().toFixed(6),
        this.map.getCenter().lng().toFixed(6),
        0
      );
    });
  };

  createRoute = function(record) {
    let coordinatesArray = [];

    if (record.configuration_map) {
      for (let i = 0; i < record.configuration_map.length; i++) {
        coordinatesArray.push(
          new google.maps.LatLng(
            record.configuration_map[i].latitude,
            record.configuration_map[i].longitude
          )
        );
      }
    }

    if (coordinatesArray.length === 0) {
      coordinatesArray.push(
        new google.maps.LatLng(
          record.latitude,
          record.longitude
        )
      );
    }

    /* create route overlay */
    let route = new google.maps.Polyline(
      this.createPolylineOptions(coordinatesArray, this.extConf)
    );
    let path = route.getPath();

    route.setMap(this.map);

    // Listener which will be called, if a vertex was moved to a new location
    google.maps.event.addListener(path, 'set_at', function() {
      this.storeRouteAsJson(route);
    });
    // Listener to add new vertex in between a route
    google.maps.event.addListener(path, 'insert_at', function() {
      this.storeRouteAsJson(route);
    });
    // Listener to remove a vertex
    google.maps.event.addListener(route, 'rightclick', function(event) {
      route.getPath().removeAt(event.vertex);
      this.storeRouteAsJson(route);
    });
    // Listener to add a new vertex. Will not be called, while inserting a vertex in between
    google.maps.event.addListener(this.map, 'click', function(event) {
      route.getPath().push(event.latLng);
    });
    // update fields for saving map position
    google.maps.event.addListener(this.map, 'dragend', function() {
      this.setLatLngFields(
        this.map.getCenter().lat().toFixed(6),
        this.map.getCenter().lng().toFixed(6),
        0
      );
    });
  };

  createRadius = function(record) {
    this.marker = new google.maps.Circle(
      this.createCircleOptions(this.map, record, this.extConf)
    );

    // update fields and marker while dragging
    google.maps.event.addListener(this.marker, 'center_changed', function() {
      this.setLatLngFields(
        this.marker.getCenter().lat().toFixed(6),
        this.marker.getCenter().lng().toFixed(6),
        this.marker.getRadius()
      );
    });

    // update fields and marker while resizing the radius
    google.maps.event.addListener(this.marker, 'radius_changed', function() {
      this.setLatLngFields(
        this.marker.getCenter().lat().toFixed(6),
        this.marker.getCenter().lng().toFixed(6),
        this.marker.getRadius()
      );
    });

    // update fields and marker when clicking on the map
    google.maps.event.addListener(this.map, 'click', function(event) {
      this.marker.setCenter(event.latLng);
      this.setLatLngFields(
        event.latLng.lat().toFixed(6),
        event.latLng.lng().toFixed(6),
        this.marker.getRadius()
      );
    });

    this.setLatLngFields(record.latitude, record.longitude, record.radius);
  };

  /**
   * Fill TCA fields for Lat and Lng with value of marker position
   *
   * @param {number} lat
   * @param {number} lng
   * @param {number} rad
   * @param {string} address
   */
  setLatLngFields = (lat, lng, rad, address) => {
    this.setFieldValue("latitude", lat);
    this.setFieldValue("longitude", lng);

    if (typeof rad !== "undefined" && rad > 0) {
      this.setFieldValue("radius", parseInt(rad));
    }

    if (typeof address !== "undefined") {
      this.setFieldValue("address", address);
    }
  };

  /**
   * Generate an uri to save all coordinates
   *
   * @param {object} route
   */
  getUriForRoute = route => {
    let routeObject = {};

    route.getPath().forEach((latLng, index) => {
      routeObject[index] = latLng.toUrlValue();
    });

    return routeObject;
  };

  /**
   * Return FieldElement from TCEFORM by fieldName
   *
   * @param {string} field
   * @returns {*|HTMLElement} jQuery object. FormEngine works with $ selectors
   */
  getFieldElement = field => {
    // Return the FieldElement which is visible to the editor
    return TYPO3.FormEngine.getFieldElement(this.buildFieldName(field), '_list');
  };

  /**
   * Build fieldName like 'data[tx_maps2_domain_model_poicollection][1][latitude]'
   *
   * @param {string} field
   * @returns {string}
   */
  buildFieldName = field => {
    return 'data[tx_maps2_domain_model_poicollection][' + this.record.uid + '][' + field + ']';
  };

  /**
   * Set field value
   *
   * @param {string} field
   * @param {string | int} value
   */
  setFieldValue = (field, value) => {
    /* getFieldName returns a jquery object via FormEngine */
    let $fieldElement = this.getFieldElement(field);

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
  storeRouteAsJson = route => {
    this.setFieldValue(
      "configuration_map",
      JSON.stringify(this.getUriForRoute(route))
    );
  };

  /**
   * Read address, send it to Google and move map/marker to new location
   */
  findAddress = () => {
    let pacInput = document.querySelector("#pac-input");
    let autocomplete = new google.maps.places.Autocomplete(pacInput, {fields: ["place_id"]});
    let geoCoder = new google.maps.Geocoder;

    autocomplete.bindTo("bounds", this.map);
    this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(pacInput);

    // Prevent submitting the BE form on enter, while selecting entry from AutoSuggest
    pacInput.addEventListener("keydown", event => {
      let pacContainer = document.querySelector(".pac-container");
      if (event.keyCode === 13 && pacContainer !== null) return false;
    });

    autocomplete.addListener("place_changed", () => {
      this.infoWindow.close();
      let place = autocomplete.getPlace();

      if (!place.place_id) {
        return;
      }

      geoCoder.geocode({"placeId": place.place_id}, (results, status) => {
        if (status !== "OK") {
          window.alert("Geocoder failed due to: " + status);
          return;
        }

        let lat = results[0].geometry.location.lat().toFixed(6);
        let lng = results[0].geometry.location.lng().toFixed(6);

        switch (this.record.collectionType) {
          case 'Point':
            this.marker.setPosition(results[0].geometry.location);
            this.marker.setVisible(true);
            this.setLatLngFields(lat, lng, 0, results[0].formatted_address);
            break;
          case 'Area':
            this.setLatLngFields(lat, lng, 0, results[0].formatted_address);
            break;
          case 'Route':
            this.setLatLngFields(lat, lng, 0, results[0].formatted_address);
            break;
          case 'Radius':
            this.marker.setCenter(results[0].geometry.location);
            this.setLatLngFields(lat, lng, this.marker.getRadius(), results[0].formatted_address);
            break;
        }

        this.map.setCenter(results[0].geometry.location);
        this.infoWindowContent.children["place-name"].textContent = place.name;
        this.infoWindowContent.children["place-id"].textContent = place.place_id;
        this.infoWindowContent.children["place-address"].textContent = results[0].formatted_address;
        this.infoWindow.open(this.map, this.marker);
      });
    });
  };
}

export default new GoogleMapsModule();
