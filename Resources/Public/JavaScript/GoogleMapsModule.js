/**
 * Module: TYPO3/CMS/Maps2/GoogleMapsModule
 */
define("TYPO3/CMS/Maps2/GoogleMapsModule", ["jquery", "gmaps"], function($, gmaps) {
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
  
  var initialize = function(element, config, extConf) {
    var markers = {};
    var map = {};

    var createMap = function() {
      map = new gmaps.Map(
        element,
        new MapOptions()
      );
    };
    createMap();
  
    var marker = new gmaps.Marker({
      map: map
    });
  
    /**
     * Create Marker
     */
    var createMarker = function() {
      var marker = new gmaps.Marker({
        position: new gmaps.LatLng(config.latitude, config.longitude),
        map: map,
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
      gmaps.event.addListener(map, 'click', function(event) {
        marker.setPosition(event.latLng);
        setLatLngFields(
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
     * @param extConf
     */
    var createArea = function(extConf) {
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
    
      area.setMap(map);
    
      // we need a listener for moving a position
      gmaps.event.addListener(path, 'set_at', function() {
        insertRouteToDb(area);
      });
      // we need a listener to add new coordinates between existing positions
      gmaps.event.addListener(path, 'insert_at', function() {
        insertRouteToDb(area);
      });
      // we need a listener to remove route coordinates
      gmaps.event.addListener(area, 'rightclick', function(event) {
        area.getPath().removeAt(event.vertex);
        insertRouteToDb(area);
      });
      // we need a listener to add new route coordinates
      gmaps.event.addListener(map, 'click', function(event) {
        area.getPath().push(event.latLng);
        insertRouteToDb(area);
      });
      // update fields for saving map position
      gmaps.event.addListener(map, 'dragend', function() {
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
     * @param extConf
     */
    var createRoute = function(extConf) {
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
    
      route.setMap(map);
    
      /* we need a listener for moving a position */
      gmaps.event.addListener(path, 'set_at', function() {
        insertRouteToDb(route);
      });
      /* we need a listener to add new coordinates between existing positions */
      gmaps.event.addListener(path, 'insert_at', function() {
        insertRouteToDb(route);
      });
      /* we need a listener to remove route coordinates */
      gmaps.event.addListener(route, 'rightclick', function(event) {
        route.getPath().removeAt(event.vertex);
        insertRouteToDb(route);
      });
      /* we need a listener to add new route coordinates */
      gmaps.event.addListener(map, 'click', function(event) {
        route.getPath().push(event.latLng);
        insertRouteToDb(route);
      });
      // update fields for saving map position
      gmaps.event.addListener(map, 'dragend', function() {
        setLatLngFields(
          map.getCenter().lat().toFixed(6),
          map.getCenter().lng().toFixed(6),
          0
        );
      });
    };
  
    /**
     * Create Radius
     *
     * @param extConf
     */
    var createRadius = function(extConf) {
      var marker = new gmaps.Circle(new CircleOptions(map, config, extConf));
    
      // update fields and marker while dragging
      gmaps.event.addListener(marker, 'center_changed', function() {
        setLatLngFields(
          marker.getCenter().lat().toFixed(6),
          marker.getCenter().lng().toFixed(6),
          marker.getRadius()
        );
      });
    
      // update fields and marker while resizing the radius
      gmaps.event.addListener(marker, 'radius_changed', function() {
        setLatLngFields(
          marker.getCenter().lat().toFixed(6),
          marker.getCenter().lng().toFixed(6),
          marker.getRadius()
        );
      });
    
      // update fields and marker when clicking on the map
      gmaps.event.addListener(map, 'click', function(event) {
        marker.setCenter(event.latLng);
        setLatLngFields(
          event.latLng.lat().toFixed(6),
          event.latLng.lng().toFixed(6),
          marker.getRadius()
        );
      });
    
      setLatLngFields(
        config.latitude,
        config.longitude,
        config.radius
      );
    
      return marker;
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
        $("#infoWindowAddress").html(address.replace(/, /gi, "<br />"));
      }
    };
  
    /**
     * Generate an uri to save all coordinates
     *
     * @param route
     */
    var getUriForRoute = function(route) {
      var routeObject = {};
      route.getPath().forEach(function(latLng, index) {
        routeObject[index] = latLng.toUrlValue();
      });
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
      // set the normal field which contains the data, which will be send by POST
      document[TBE_EDITOR.formname][fieldName].value = value;
    };
  
    /**
     * Get field value
     *
     * @param field
     * @param hiddenRecord
     * @returns string
     */
    var getFieldValue = function(field, hiddenRecord) {
      var fieldName = createFieldName(field, hiddenRecord);
      return document[TBE_EDITOR.formname][fieldName].value;
    };
  
    /**
     * if user has moved marker after searching for an address he can reset marker to its original position
     *
     * @param marker
     */
    var resetMarkerToAddress = function(marker) {
      $("#txMaps2Reset").on("click", function() {
        // Move map and marker to new position
        var latLng = new gmaps.LatLng(config.latitudeOrig, config.longitudeOrig);
        map.setCenter(latLng);
      
        if (typeof marker.setPosition === "function") {
          marker.setPosition(latLng);
          setLatLngFields(config.latitudeOrig, config.longitudeOrig, 0);
        } else {
          marker.setCenter(latLng);
          setLatLngFields(config.latitudeOrig, config.longitudeOrig, marker.getRadius());
        }
      });
    };
  
    /**
     * Save coordinated to DB
     *
     * @param route
     */
    var insertRouteToDb = function(route) {
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
     */
    var findAddress = function() {
      var input = document.getElementById('pac-input');
      var autocomplete = new gmaps.places.Autocomplete(input, {placeIdOnly: true});
      var geocoder = new gmaps.Geocoder;
      var infowindow = new gmaps.InfoWindow();
      var infowindowContent = document.getElementById('infowindow-content');
      autocomplete.bindTo('bounds', map);
      map.controls[gmaps.ControlPosition.TOP_LEFT].push(input);
      infowindow.setContent(infowindowContent);
  
      marker.addListener('click', function() {
        infowindow.open(map, marker);
      });
  
      autocomplete.addListener('place_changed', function() {
        infowindow.close();
        var place = autocomplete.getPlace();
    
        if (!place.place_id) {
          return;
        }
    
        geocoder.geocode({'placeId': place.place_id}, function(results, status) {
          if (status !== 'OK') {
            window.alert('Geocoder failed due to: ' + status);
            return;
          }
          var lat = results[0].geometry.location.lat().toFixed(6);
          var lng = results[0].geometry.location.lng().toFixed(6);
  
          if (typeof marker.setPosition === "function") {
            marker.setPosition(results[0].geometry.location);
            setLatLngFields(lat, lng, 0, results[0].formatted_address);
          } else {
            marker.setCenter(results[0].geometry.location);
            setLatLngFields(lat, lng, marker.getRadius(), results[0].formatted_address);
            modifyMarkerInDb(lat, lng); // save radius to DB
          }

          map.setCenter(results[0].geometry.location);
          // Set the position of the marker using the place ID and location.
          marker.setPlace({
            placeId: place.place_id,
            location: results[0].geometry.location
          });
          marker.setVisible(true);
          infowindowContent.children['place-name'].textContent = place.name;
          infowindowContent.children['place-id'].textContent = place.place_id;
          infowindowContent.children['place-address'].textContent =
            results[0].formatted_address;
          infowindow.open(map, marker);
        });
      });
    };
  
    /**
     * Modify Marker in DB
     *
     * @param lat
     * @param lng
     * @param rad
     */
    var modifyMarkerInDb = function(lat, lng, rad) {
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
    
    switch (config.collectionType) {
      case "Point":
        marker = createMarker();
        break;
      case "Area":
        createArea(extConf);
        break;
      case "Route":
        createRoute(extConf);
        break;
      case "Radius":
        marker = createRadius(extConf);
        break;
    }
  
    if (marker !== null) {
      findAddress(marker);
      resetMarkerToAddress(marker);
    }
  
    if (config.latitude && config.longitude) {
      map.setCenter(new gmaps.LatLng(config.latitude, config.longitude));
    } else {
      // Fallback
      map.setCenter(new gmaps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
    }
  
    // if maps2 was inserted in (bootstrap) tabs, we have to re-render the map
    $("ul.t3js-tabs a[data-toggle='tab']:first").on("shown.bs.tab", function() {
      google.maps.event.trigger(map, 'resize');
      if (config.latitude && config.longitude) {
        map.setCenter(new gmaps.LatLng(config.latitude, config.longitude));
      } else {
        map.setCenter(new gmaps.LatLng(extConf.defaultLatitude, extConf.defaultLongitude));
      }
    });
  };

  /**
   * Return a function that gets DOM elements that are checked if suggest is already initialized
   * @exports TYPO3/CMS/Backend/FormEngineSuggest
   */
  return function() {
    $element = $('#maps2ConfigurationMap');
    initialize(
      $element.get(0),
      $element.data("config"),
      $element.data("extconf")
    );
  };
});
