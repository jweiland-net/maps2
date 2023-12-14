import $ from 'jquery';
import FormEngine from "@typo3/backend/form-engine.js";

class OpenStreetMapModule {
  marker = {};
  map = {};
  $element = {};

  constructor() {
    this.$element = $("#maps2ConfigurationMap");

    let record = this.$element.data("record");
    let extConf = this.$element.data("extconf");
    let map = this.createMap();
    let marker = {};

    switch (record.collection_type) {
      case "Point":
        marker = this.createMarker(record);
        break;
      case "Area":
        this.createArea(record, extConf);
        break;
      case "Route":
        this.createRoute(record, extConf);
        break;
      case "Radius":
        marker = this.createRadius(record, extConf);
        break;
    }

    this.findAddress(record, marker);

    if (record.latitude && record.longitude) {
      this.map.panTo([record.latitude, record.longitude]);
    } else {
      // Fallback
      this.map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
    }

    // If maps2 was inserted in (bootstrap) tabs, we have to re-render the map
    $("ul.t3js-tabs a[data-bs-toggle='tab']:eq(1)").on("shown.bs.tab", () => {
      this.map.invalidateSize();
      if (record.latitude && record.longitude) {
        this.map.panTo([record.latitude, record.longitude]);
      } else {
        // Fallback
        this.map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
      }
    });
  }

  createMap = () => {
    this.map = L.map(
      this.$element.get(0),
      {
        editable: true
      }).setView([51.505, -0.09], 15);

    L.tileLayer(location.protocol + '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
      id: 'mapbox.streets'
    }).addTo(this.map);
  };

  createMarker = record => {
    let osm = this;
    let marker = L.marker(
      [record.latitude, record.longitude],
      {
        'draggable': true
      }
    ).addTo(this.map);

    // update fields and marker while dragging
    marker.on('dragend', () => {
      osm.setLatLngFields(
        record,
        marker.getLatLng().lat.toFixed(6),
        marker.getLatLng().lng.toFixed(6),
        0
      );
    });

    // update fields and marker when clicking on the map
    this.map.on('click', event => {
      marker.setLatLng(event.latlng);
      osm.setLatLngFields(
        record,
        event.latlng.lat.toFixed(6),
        event.latlng.lng.toFixed(6),
        0
      );
    });

    return marker;
  };

  createArea = (record, extConf) => {
    let osm = this;
    let area = {};
    let coordinatesArray = [];
    let options = {
      color: extConf.strokeColor,
      weight: extConf.strokeWeight,
      opacity: extConf.strokeOpacity,
      fillColor: extConf.fillColor,
      fillOpacity: extConf.fillOpacity
    };

    if (record.configuration_map) {
      for (let i = 0; i < record.configuration_map.length; i++) {
        coordinatesArray.push([
          record.configuration_map[i].latitude,
          record.configuration_map[i].longitude]
        );
      }
    }

    if (coordinatesArray.length === 0) {
      area = this.map.editTools.startPolygon(null, options);
    } else {
      area = L.polygon(coordinatesArray, options).addTo(this.map);
      area.enableEdit();
    }

    this.map.on('moveend', event => {
      osm.setLatLngFields(
        record,
        event.target.getCenter().lat.toFixed(6),
        event.target.getCenter().lng.toFixed(6),
        0
      );
    });
    this.map.on("editable:vertex:new", event => {
      osm.storeRouteAsJson(record, area.getLatLngs()[0]);
    });
    this.map.on("editable:vertex:deleted", event => {
      osm.storeRouteAsJson(record, area.getLatLngs()[0]);
    });
    this.map.on("editable:vertex:dragend", event => {
      osm.storeRouteAsJson(record, area.getLatLngs()[0]);
    });
  };

  createRoute = (record, extConf) => {
    let osm = this;
    let route = {};
    let coordinatesArray = [];
    let options = {
      color: extConf.strokeColor,
      weight: extConf.strokeWeight,
      opacity: extConf.strokeOpacity
    };

    if (record.configuration_map) {
      for (let i = 0; i < record.configuration_map.length; i++) {
        coordinatesArray.push([
          record.configuration_map[i].latitude,
          record.configuration_map[i].longitude]
        );
      }
    }

    if (coordinatesArray.length === 0) {
      route = this.map.editTools.startPolyline(null, options);
    } else {
      route = L.polyline(coordinatesArray, options).addTo(this.map);
      route.enableEdit();
    }

    this.map.on('moveend', event => {
      osm.setLatLngFields(
        record,
        event.target.getCenter().lat.toFixed(6),
        event.target.getCenter().lng.toFixed(6),
        0
      );
    });
    this.map.on("editable:vertex:new", event => {
      osm.storeRouteAsJson(record, route.getLatLngs());
    });
    this.map.on("editable:vertex:deleted", event => {
      osm.storeRouteAsJson(record, route.getLatLngs());
    });
    this.map.on("editable:vertex:dragend", event => {
      osm.storeRouteAsJson(record, route.getLatLngs());
    });
  };

  createRadius = (record, extConf) => {
    let osm = this;
    let marker = L.circle(
      [record.latitude, record.longitude],
      {
        color: extConf.strokeColor,
        opacity: extConf.strokeOpacity,
        weight: extConf.strokeWeight,
        fillColor: extConf.fillColor,
        fillOpacity: extConf.fillOpacity,
        radius: record.radius ? record.radius : extConf.defaultRadius
      }
    ).addTo(this.map);

    let editor = marker.enableEdit();

    // Update fields and marker while dragging
    marker.on('editable:dragend editable:vertex:dragend', event => {
      osm.setLatLngFields(
        record,
        marker.getLatLng().lat.toFixed(6),
        marker.getLatLng().lng.toFixed(6),
        marker.getRadius()
      );
    });

    return marker;
  };

  /**
   * Fill TCA fields for Lat and Lng with value of marker position
   *
   * @param number lat
   * @param number lng
   * @param number rad
   * @param string address
   */
  setLatLngFields = (record, lat, lng, rad, address) => {
    this.setFieldValue(record, "latitude", lat);
    this.setFieldValue(record, "longitude", lng);

    if (typeof rad !== "undefined" && rad > 0) {
      this.setFieldValue(record, "radius", parseInt(rad));
    }

    if (typeof address !== "undefined") {
      this.setFieldValue(record, "address", address);
    }
  };

  /**
   * Generate an uri to save all coordinates
   *
   * @param coordinates
   */
  getUriForCoordinates = coordinates => {
    let routeObject = {};

    for (let index = 0; index < coordinates.length; index++) {
      routeObject[index] = coordinates[index]['lat'] + ',' + coordinates[index]['lng'];
    }

    return routeObject;
  };

  /**
   * Return FieldElement from TCEFORM by fieldName
   *
   * @param field
   * @returns {*|HTMLElement} jQuery object. FormEngine works with $ selectors
   */
  getFieldElement = (record, field) => {
    // Return the FieldElement which is visible to the editor
    return FormEngine.getFieldElement(this.buildFieldName(record, field), '_list');
  };

  /**
   * Build fieldName like 'data[tx_maps2_domain_model_poicollection][1][latitude]'
   *
   * @param record
   * @param field
   * @returns {string}
   */
  buildFieldName = (record, field) => {
    return 'data[tx_maps2_domain_model_poicollection][' + record.uid + '][' + field + ']';
  };

  /**
   * Set field value
   *
   * @param record
   * @param field
   * @param value
   */
  setFieldValue = (record, field, value) => {
    let $fieldElement = this.getFieldElement(record, field);
    if ($fieldElement && $fieldElement.length) {
      $fieldElement.val(value);
      $fieldElement.triggerHandler("change");
    }
  };

  /**
   * Store route/area path into configuration_map as JSON
   *
   * @param coordinates
   */
  storeRouteAsJson = (record, coordinates) => {
    this.setFieldValue(
      record,
      "configuration_map",
      JSON.stringify(this.getUriForCoordinates(coordinates))
    );
  };

  /**
   * read address, send it to OpenStreetMap and move map/marker to new location
   */
  findAddress = (record, marker) => {
    let osm = this;
    let $pacSearch = $(document.getElementById("pac-search"));

    // Prevent submitting the BE form on enter
    $pacSearch.keydown(event => {
      if (event.which === 13) {
        if ($pacSearch.val()) {
          $.ajax({
            type: "GET",
            url: 'https://nominatim.openstreetmap.org/search?q=' + encodeURI($pacSearch.val()) + '&format=json&addressdetails=1',
            dataType: 'json'
          }).done(data => {
            if (data.length === 0) {
              alert('Address not found');
            } else {
              let lat = parseFloat(data[0].lat).toFixed(6);
              let lng = parseFloat(data[0].lon).toFixed(6);
              let address = data[0].address;
              let formattedAddress = osm.getFormattedAddress(address);

              switch (record.collection_type) {
                case 'Point':
                  marker.setLatLng([lat, lng]);
                  osm.setLatLngFields(record, lat, lng, 0, formattedAddress);
                  break;
                case 'Area':
                  osm.setLatLngFields(record, lat, lng, 0, formattedAddress);
                  break;
                case 'Route':
                  osm.setLatLngFields(record, lat, lng, 0, formattedAddress);
                  break;
                case 'Radius':
                  marker.setLatLng([lat, lng]);
                  marker.editor.updateResizeLatLng();
                  marker.editor.reset();
                  osm.setLatLngFields(record, lat, lng, marker.getRadius(), formattedAddress);
                  break;
              }

              osm.map.panTo([lat, lng]);
            }
          }).fail(() => {
            // alert("Shit");
          });
        }

        return false;
      }
    });
  };

  /**
   * format address from ajax result
   *
   * @param address
   * @returns {string}
   */
  getFormattedAddress = address => {
    let formattedAddress = '';
    let city = '';

    if (address.hasOwnProperty('road')) {
      formattedAddress += address.road;
    }
    if (address.hasOwnProperty('house_number')) {
      formattedAddress += ' ' + address.house_number;
    }
    if (address.hasOwnProperty('postcode')) {
      formattedAddress += ', ' + address.postcode;
    }

    if (address.hasOwnProperty('village')) {
      city = address.village;
    }
    if (address.hasOwnProperty('town')) {
      city = address.town;
    }
    if (address.hasOwnProperty('city')) {
      city = address.city;
    }
    formattedAddress += ' ' + city;

    if (address.hasOwnProperty('country')) {
      formattedAddress += ', ' + address.country;
    }

    return formattedAddress;
  };
}

export default new OpenStreetMapModule();
