/**
 * Module: TYPO3/CMS/Maps2/GoogleMapsModule
 */
define("TYPO3/CMS/Maps2/GoogleMapsModule", ["jquery", "gmaps"], function($, gmaps) {

  var setLatLngFields,
    getFieldValue,
    setFieldValue,
    createFieldName,
    insertRouteToDb,
    getUriForRoute;

  /**
   * Create a MapOptions object which can be assigned to the Map object of Google
   *
   * @constructor
   */
  function MapOptions() {
    this.zoom = 12;
    this.mapTypeId = gmaps.MapTypeId.ROADMAP;
  }

  /**
   * Create CircleOptions which can be assigned to the Circle object of Google
   *
   * @param map
   * @param config
   * @param extConf
   * @constructor
   */
  function CircleOptions(map, config, extConf) {
    this.map = map;
    this.center = new gmaps.LatLng(config.latitude, config.longitude);
    this.strokeColor = extConf.strokeColor;
    this.strokeOpacity = extConf.strokeOpacity;
    this.strokeWeight = extConf.strokeWeight;
    this.fillColor = extConf.fillColor;
    this.fillOpacity = extConf.fillOpacity;
    this.editable = true;
    if (config.radius === 0) {
      this.radius = extConf.defaultRadius;
    } else {
      this.radius = config.radius;
    }
  }

  /**
   * Create PolygonOptions which can be assigned to the Polygon object of Google
   *
   * @param paths
   * @param extConf
   * @constructor
   */
  function PolygonOptions(paths, extConf) {
    this.paths = paths;
    this.strokeColor = extConf.strokeColor;
    this.strokeOpacity = extConf.strokeOpacity;
    this.strokeWeight = extConf.strokeWeight;
    this.fillColor = extConf.fillColor;
    this.fillOpacity = extConf.fillOpacity;
    this.editable = true;
  }

  /**
   * Create PolylineOptions which can be assigned to the Polyline object of Google
   *
   * @param paths
   * @param extConf
   * @constructor
   */
  function PolylineOptions(paths, extConf) {
    this.path = paths;
    this.strokeColor = extConf.strokeColor;
    this.strokeOpacity = extConf.strokeOpacity;
    this.strokeWeight = extConf.strokeWeight;
    this.editable = true;
  }

  /**
   * Create Map Object
   *
   * @param config
   * @param extConf
   * @constructor
   */
  function TxMaps2(config, extConf) {
    this.markers = {};
    this.$element = $("#maps2ConfigurationMap");
    this.map = {};
    this.marker = null;

    this.createMap();

    switch (config.collectionType) {
      case "Point":
        this.marker = this.createMarker(config);
        break;
      case "Area":
        this.createArea(config, extConf);
        break;
      case "Route":
        this.createRoute(config, extConf);
        break;
      case "Radius":
        this.marker = this.createRadius(config, extConf);
        break;
    }

    if (this.marker !== null) {
      this.findAddressOnClick(config, this.marker);
      this.resetMarkerToAddress(config, this.marker);
    }

    if (config.latitude && config.longitude) {
      this.map.setCenter(new gmaps.LatLng(config.latitude, config.longitude));
    } else {
      // Fallback
      this.map.setCenter(new gmaps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
    }
  }

  /**
   * Create Map
   */
  TxMaps2.prototype.createMap = function() {
    this.map = new gmaps.Map(
      this.$element.get(0),
      new MapOptions()
    );
  };

  /**
   * Create Marker
   *
   * @param config
   */
  TxMaps2.prototype.createMarker = function(config) {
    var marker = new gmaps.Marker({
      position: new gmaps.LatLng(config.latitude, config.longitude),
      map: this.map,
      draggable: true
    });

    // update fields and marker while dragging
    gmaps.event.addListener(marker, 'dragend', function() {
      setLatLngFields(
        config,
        marker.getPosition().lat().toFixed(6),
        marker.getPosition().lng().toFixed(6),
        0
      );
    });

    // update fields and marker when clicking on the map
    gmaps.event.addListener(this.map, 'click', function(event) {
      marker.setPosition(event.latLng);
      setLatLngFields(
        config,
        event.latLng.lat().toFixed(6),
        event.latLng.lng().toFixed(6),
        0
      );
    });

    return marker;
  };

  /**
   * Create Area
   *
   * @param config
   * @param extConf
   */
  TxMaps2.prototype.createArea = function(config, extConf) {
    var coordinatesArray = [];

    if (typeof config.pois !== 'undefined') {
      for (var i = 0; i < config.pois.length; i++) {
        coordinatesArray.push(new gmaps.LatLng(config.pois[i].latitude, config.pois[i].longitude));
      }
    }

    if (coordinatesArray.length === 0) {
      coordinatesArray.push(new gmaps.LatLng(config.latitude, config.longitude));
    }

    var area = new gmaps.Polygon(new PolygonOptions(coordinatesArray, extConf));
    var path = area.getPath();
    var map = this.map;

    area.setMap(this.map);

    // we need a listener for moving a position
    gmaps.event.addListener(path, 'set_at', function() {
      insertRouteToDb(config, area);
    });
    // we need a listener to add new coordinates between existing positions
    gmaps.event.addListener(path, 'insert_at', function() {
      insertRouteToDb(config, area);
    });
    // we need a listener to remove route coordinates
    gmaps.event.addListener(area, 'rightclick', function(event) {
      area.getPath().removeAt(event.vertex);
      insertRouteToDb(config, area);
    });
    // we need a listener to add new route coordinates
    gmaps.event.addListener(this.map, 'click', function(event) {
      area.getPath().push(event.latLng);
      insertRouteToDb(config, area);
    });
    // update fields for saving map position
    gmaps.event.addListener(this.map, 'dragend', function() {
      setLatLngFields(
        config,
        map.getCenter().lat().toFixed(6),
        map.getCenter().lng().toFixed(6),
        0
      );
    });
  };

  /**
   * Create Route
   *
   * @param config
   * @param extConf
   */
  TxMaps2.prototype.createRoute = function(config, extConf) {
    var coordinatesArray = [];

    if (typeof config.pois !== 'undefined') {
      for (var i = 0; i < config.pois.length; i++) {
        coordinatesArray.push(new gmaps.LatLng(config.pois[i].latitude, config.pois[i].longitude));
      }
    }

    if (coordinatesArray.length === 0) {
      coordinatesArray.push(new gmaps.LatLng(config.latitude, config.longitude));
    }

    /* create route overlay */
    var route = new gmaps.Polyline(new PolylineOptions(coordinatesArray, extConf));
    var path = route.getPath();
    var map = this.map;

    route.setMap(this.map);

    /* we need a listener for moving a position */
    gmaps.event.addListener(path, 'set_at', function() {
      insertRouteToDb(config, route);
    });
    /* we need a listener to add new coordinates between existing positions */
    gmaps.event.addListener(path, 'insert_at', function() {
      insertRouteToDb(config, route);
    });
    /* we need a listener to remove route coordinates */
    gmaps.event.addListener(route, 'rightclick', function(event) {
      route.getPath().removeAt(event.vertex);
      insertRouteToDb(config, route);
    });
    /* we need a listener to add new route coordinates */
    gmaps.event.addListener(map, 'click', function(event) {
      route.getPath().push(event.latLng);
      insertRouteToDb(config, route);
    });
    /* update fields for saving map position */
    gmaps.event.addListener(map, 'dragend', function() {
      setLatLngFields(map.getCenter().lat().toFixed(6), map.getCenter().lng().toFixed(6), 0);
    });
  };

  /**
   * Create Radius
   *
   * @param config
   * @param extConf
   */
  TxMaps2.prototype.createRadius = function(config, extConf) {
    var marker = new gmaps.Circle(new CircleOptions(this.map, config, extConf));

    // update fields and marker while dragging
    gmaps.event.addListener(marker, 'center_changed', function() {
      setLatLngFields(
        config,
        marker.getCenter().lat().toFixed(6),
        marker.getCenter().lng().toFixed(6),
        marker.getRadius()
      );
    });

    // update fields and marker while resizing the radius
    gmaps.event.addListener(marker, 'radius_changed', function() {
      setLatLngFields(
        config,
        marker.getCenter().lat().toFixed(6),
        marker.getCenter().lng().toFixed(6),
        marker.getRadius()
      );
    });

    // update fields and marker when clicking on the map
    gmaps.event.addListener(this.map, 'click', function(event) {
      marker.setCenter(event.latLng);
      setLatLngFields(
        config,
        event.latLng.lat().toFixed(6),
        event.latLng.lng().toFixed(6),
        marker.getRadius()
      );
    });

    this.setLatLngFields(
      config,
      config.latitude,
      config.longitude,
      config.radius
    );

    return marker;
  };

  /**
   * Fill TCA fields for Lat and Lng with value of marker position
   *
   * @param config
   * @param lat
   * @param lng
   * @param rad
   * @param address
   */
  TxMaps2.prototype.setLatLngFields = setLatLngFields = function(config, lat, lng, rad, address) {
    setFieldValue(config, "latitude", lat);
    setFieldValue(config, "longitude", lng);
    TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "latitude", createFieldName(config, "latitude", false));
    TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "longitude", createFieldName(config, "longitude", false));

    if (typeof rad !== "undefined" && rad > 0) {
      setFieldValue(config, "radius", parseInt(rad));
      TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "radius", createFieldName(config, "radius", false));
    }

    if (typeof address !== "undefined") {
      setFieldValue(config, "address", address);
      TBE_EDITOR.fieldChanged("tx_maps2_domain_model_poicollection", config.uid, "address", createFieldName(config, "address", false));
      $("#infoWindowAddress").html(address.replace(/, /gi, "<br />"));
    }
  };

  /**
   * Generate an uri to save all coordinates
   *
   * @param route
   */
  TxMaps2.prototype.getUriForRoute = getUriForRoute = function(route) {
    var routeObject = {};
    route.getPath().forEach(function(latLng, index) {
      routeObject[index] = latLng.toUrlValue();
    });
    return routeObject;
  };

  /**
   * Create field value
   *
   * @param config
   * @param field
   * @param hiddenRecord
   * @returns {string}
   */
  TxMaps2.prototype.createFieldName = createFieldName = function(config, field, hiddenRecord) {
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
  TxMaps2.prototype.setFieldValue = setFieldValue = function(config, field, value) {
    var fieldName = createFieldName(config, field, true);
    // set the old (< TYPO3 7.5) hidden record fields "*_hr"
    if (typeof document[TBE_EDITOR.formname][fieldName] !== 'undefined') {
      document[TBE_EDITOR.formname][fieldName].value = value;
    }
    // set the new (>= TYPO3 7.5) data fields "data-formengine-input-name"
    fieldName = createFieldName(config, field, false);
    var $humanReadableField = $('[data-formengine-input-name="' + fieldName + '"]');
    if ($humanReadableField.length) {
      $humanReadableField.val(value);
    }
    // set the normal field which contains the data, which will be send by POST
    document[TBE_EDITOR.formname][fieldName].value = value;
  };

  /**
   * Get field value
   *
   * @param config
   * @param field
   * @param hiddenRecord
   * @returns string
   */
  TxMaps2.prototype.getFieldValue = getFieldValue = function(config, field, hiddenRecord) {
    var fieldName = createFieldName(config, field, hiddenRecord);
    return document[TBE_EDITOR.formname][fieldName].value;
  };

  /**
   * if user has moved marker after searching for an address he can reset marker to its original position
   *
   * @param config
   * @param marker
   */
  TxMaps2.prototype.resetMarkerToAddress = function(config, marker) {
    var map = this.map;
    $("#txMaps2Reset").on("click", function() {
      // Move map and marker to new position
      var latLng = new gmaps.LatLng(config.latitudeOrig, config.longitudeOrig);
      map.setCenter(latLng);

      if (typeof marker.setPosition === "function") {
        marker.setPosition(latLng);
        setLatLngFields(config, config.latitudeOrig, config.longitudeOrig, 0);
      } else {
        marker.setCenter(latLng);
        setLatLngFields(config, config.latitudeOrig, config.longitudeOrig, marker.getRadius());
      }
    });
  };

  /**
   * Save coordinated to DB
   *
   * @param config
   * @param route
   */
  TxMaps2.prototype.insertRouteToDb = insertRouteToDb = function(config, route) {
    $.ajax({
      type: "GET",
      url: TYPO3.settings.ajaxUrls['maps2Ajax'],
      data: {
        tx_maps2_maps2: {
          objectName: "InsertRoute",
          hash: config.hash,
          arguments: {
            uid: config.uid,
            route: getUriForRoute(route)
          }
        }
      }
    });
  };

  /**
   * read address, send it to Google and move map/marker to new location
   *
   * @param config
   * @param marker
   */
  TxMaps2.prototype.findAddressOnClick = function(config, marker) {
    var map = this.map;
    $("#txMaps2Update").on("click", function() {
      var address = getFieldValue(config, 'address');
      var geocoder = new gmaps.Geocoder();
      var geocoderRequest = {
        address: address
      };
      geocoder.geocode(geocoderRequest, function(results, status) {
        if (status === "OK") {
          var lat = results[0].geometry.location.lat().toFixed(6);
          var lng = results[0].geometry.location.lng().toFixed(6);

          // Move map and marker to new position
          map.setCenter(results[0].geometry.location);

          if (typeof marker.setPosition === "function") {
            marker.setPosition(results[0].geometry.location);
            setLatLngFields(config, lat, lng, 0, results[0].formatted_address);
          } else {
            marker.setCenter(results[0].geometry.location);
            setLatLngFields(config, lat, lng, marker.getRadius(), results[0].formatted_address);
          }

          // save new location
          modifyMarkerInDb(config, lat, lng);
        } else {
          switch (status) {
            case "ERROR":
            default:
              alert("There was a problem contacting the Google servers.");
              break;
            case "INVALID_REQUEST":
              alert("This GeocoderRequest was invalid.");
              break;
            case "OVER_QUERY_LIMIT":
              alert("The webpage has gone over the requests limit in too short a period of time.");
              break;
            case "REQUEST_DENIED":
              alert("The webpage is not allowed to use the geocoder.");
              break;
            case "UNKNOWN_ERROR":
              alert("A geocoding request could not be processed due to a server error. The request may succeed if you try again.");
              break;
            case "ZERO_RESULTS":
              alert("No result was found for this GeocoderRequest.");
              break;
          }
        }
      });
    });
  };

  /**
   * Modify Marker in DB
   *
   * @param config
   * @param lat
   * @param lng
   * @param rad
   */
  TxMaps2.prototype.modifyMarkerInDb = modifyMarkerInDb = function(config, lat, lng, rad) {
    $.ajax({
      type: "POST",
      url: TYPO3.settings.ajaxUrls['maps2Ajax'],
      data: {
        tx_maps2_maps2: {
          objectName: "ModifyMarker",
          hash: config.hash,
          arguments: {
            uid: config.uid,
            radius: rad,
            coords: {
              latitude: lat,
              longitude: lng
            }
          }
        }
      }
    }).done(function() {
      // alert("Juhuu");
    }).fail(function() {
      // alert("Shit");
    });
  };

  var $element = $("#maps2ConfigurationMap");
  new TxMaps2(
    $element.data("config"),
    $element.data("extconf")
  );
});
