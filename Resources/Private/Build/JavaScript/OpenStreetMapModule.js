import { ExtConf, PoiCollection } from '@jweiland/maps2/Classes.js';
import FormEngine from "@typo3/backend/form-engine.js";

class OpenStreetMapModule {
  "use strict"

  /**
   * @type {HTMLElement}
   */
  element = {};

  /**
   * @type {L.Map}
   */
  map = {};

  constructor() {
    this.element = document.querySelector("#maps2ConfigurationMap");

    /**
     * @type {ExtConf}
     */
    let extConf = new ExtConf(JSON.parse(this.element.dataset.extConf));

    /**
     * @type {PoiCollection}
     */
    let poiCollection = new PoiCollection(JSON.parse(this.element.dataset.poiCollection));

    /**
     * @type {L.Marker}
     */
    let marker = {};

    this.createMap();

    switch (poiCollection.collectionType) {
      case "Point":
        marker = this.createMarker(poiCollection);
        break;
      case "Area":
        this.createArea(poiCollection, extConf);
        break;
      case "Route":
        this.createRoute(poiCollection, extConf);
        break;
      case "Radius":
        marker = this.createRadius(poiCollection, extConf);
        break;
    }

    this.findAddress(poiCollection, marker);

    if (poiCollection.latitude && poiCollection.longitude) {
      this.map.panTo([poiCollection.latitude, poiCollection.longitude]);
    } else {
      // Fallback
      this.map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
    }

    // If maps2 was inserted in (bootstrap) tabs, we have to re-render the map
    document.querySelector("ul.t3js-tabs li:nth-of-type(2) a[data-bs-toggle='tab']").addEventListener("shown.bs.tab", () => {
      this.map.invalidateSize();
      if (poiCollection.latitude && poiCollection.longitude) {
        this.map.panTo([poiCollection.latitude, poiCollection.longitude]);
      } else {
        // Fallback
        this.map.panTo([extConf.defaultLatitude, extConf.defaultLongitude]);
      }
    });
  }

  createMap = () => {
    this.map = L.map(
      this.element,
      {
        editable: true
      }).setView([51.505, -0.09], 15);

    L.tileLayer(location.protocol + "//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +  '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
      id: "mapbox.streets"
    }).addTo(this.map);
  };

  createMarker = poiCollection => {
    let osm = this;
    let marker = L.marker(
      [poiCollection.latitude, poiCollection.longitude],
      {
        "draggable": true
      }
    ).addTo(this.map);

    // update fields and marker while dragging
    marker.on("dragend", () => {
      osm.setLatLngFields(
        poiCollection,
        marker.getLatLng().lat.toFixed(6),
        marker.getLatLng().lng.toFixed(6),
        0
      );
    });

    // update fields and marker when clicking on the map
    this.map.on("click", event => {
      marker.setLatLng(event.latlng);
      osm.setLatLngFields(
        poiCollection,
        event.latlng.lat.toFixed(6),
        event.latlng.lng.toFixed(6),
        0
      );
    });

    return marker;
  };

  createArea = (poiCollection, extConf) => {
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

    if (poiCollection.configurationMap) {
      for (let i = 0; i < poiCollection.configurationMap.length; i++) {
        coordinatesArray.push([
          poiCollection.configurationMap[i].latitude,
          poiCollection.configurationMap[i].longitude]
        );
      }
    }

    if (coordinatesArray.length === 0) {
      area = this.map.editTools.startPolygon(null, options);
    } else {
      area = L.polygon(coordinatesArray, options).addTo(this.map);
      area.enableEdit();
    }

    this.map.on("moveend", event => {
      osm.setLatLngFields(
        poiCollection,
        event.target.getCenter().lat.toFixed(6),
        event.target.getCenter().lng.toFixed(6),
        0
      );
    });
    this.map.on("editable:vertex:new", event => {
      osm.storeRouteAsJson(poiCollection, area.getLatLngs()[0]);
    });
    this.map.on("editable:vertex:deleted", event => {
      osm.storeRouteAsJson(poiCollection, area.getLatLngs()[0]);
    });
    this.map.on("editable:vertex:dragend", event => {
      osm.storeRouteAsJson(poiCollection, area.getLatLngs()[0]);
    });
  };

  createRoute = (poiCollection, extConf) => {
    let osm = this;
    let route = {};
    let coordinatesArray = [];
    let options = {
      color: extConf.strokeColor,
      weight: extConf.strokeWeight,
      opacity: extConf.strokeOpacity
    };

    if (poiCollection.configurationMap) {
      for (let i = 0; i < poiCollection.configurationMap.length; i++) {
        coordinatesArray.push([
          poiCollection.configurationMap[i].latitude,
          poiCollection.configurationMap[i].longitude]
        );
      }
    }

    if (coordinatesArray.length === 0) {
      route = this.map.editTools.startPolyline(null, options);
    } else {
      route = L.polyline(coordinatesArray, options).addTo(this.map);
      route.enableEdit();
    }

    this.map.on("moveend", event => {
      osm.setLatLngFields(
        poiCollection,
        event.target.getCenter().lat.toFixed(6),
        event.target.getCenter().lng.toFixed(6),
        0
      );
    });
    this.map.on("editable:vertex:new", event => {
      osm.storeRouteAsJson(poiCollection, route.getLatLngs());
    });
    this.map.on("editable:vertex:deleted", event => {
      osm.storeRouteAsJson(poiCollection, route.getLatLngs());
    });
    this.map.on("editable:vertex:dragend", event => {
      osm.storeRouteAsJson(poiCollection, route.getLatLngs());
    });
  };

  createRadius = (poiCollection, extConf) => {
    let osm = this;
    let marker = L.circle(
      [poiCollection.latitude, poiCollection.longitude],
      {
        color: extConf.strokeColor,
        opacity: extConf.strokeOpacity,
        weight: extConf.strokeWeight,
        fillColor: extConf.fillColor,
        fillOpacity: extConf.fillOpacity,
        radius: poiCollection.radius ? poiCollection.radius : extConf.defaultRadius
      }
    ).addTo(this.map);

    let editor = marker.enableEdit();

    // Update fields and marker while dragging
    marker.on("editable:dragend editable:vertex:dragend", event => {
      osm.setLatLngFields(
        poiCollection,
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
  setLatLngFields = (poiCollection, lat, lng, rad, address) => {
    this.setFieldValue(poiCollection, "latitude", lat);
    this.setFieldValue(poiCollection, "longitude", lng);

    if (typeof rad !== "undefined" && rad > 0) {
      this.setFieldValue(poiCollection, "radius", parseInt(rad));
    }

    if (typeof address !== "undefined") {
      this.setFieldValue(poiCollection, "address", address);
    }
  };

  /**
   * Generate an uri to save all coordinates
   *
   * @param {array} coordinates
   * @return {object}
   */
  getUriForCoordinates = coordinates => {
    let routeObject = {};

    for (let index = 0; index < coordinates.length; index++) {
      routeObject[index] = coordinates[index]["lat"] + "," + coordinates[index]["lng"];
    }

    return routeObject;
  };

  /**
   * Return FieldElement from TCEFORM by fieldName
   *
   * @param field
   * @returns {*|HTMLElement} jQuery object. FormEngine works with $ selectors
   */
  getFieldElement = (poiCollection, field) => {
    // Return the FieldElement which is visible to the editor
    return FormEngine.getFieldElement(this.buildFieldName(poiCollection, field), "_list");
  };

  /**
   * Build fieldName like "data[tx_maps2_domain_model_poicollection][1][latitude]"
   *
   * @param poiCollection
   * @param field
   * @returns {string}
   */
  buildFieldName = (poiCollection, field) => {
    return "data[tx_maps2_domain_model_poicollection][" + poiCollection.uid + "][" + field + "]";
  };

  /**
   * Set field value
   *
   * @param {PoiCollection} poiCollection
   * @param {string} field
   * @param {string | number} value
   */
  setFieldValue = (poiCollection, field, value) => {
    /* getFieldName returns a jquery object via FormEngine */
    let $fieldElement = this.getFieldElement(poiCollection, field);

    if ($fieldElement && $fieldElement.length) {
      let humanReadableField = $fieldElement.get(0);
      humanReadableField.value = value;
      humanReadableField.dispatchEvent(new Event('change'));
    }
  };

  /**
   * Store route/area path into configurationMap as JSON
   *
   * @param {PoiCollection} poiCollection
   * @param coordinates
   */
  storeRouteAsJson = (poiCollection, coordinates) => {
    this.setFieldValue(
      poiCollection,
      "configuration_map",
      JSON.stringify(this.getUriForCoordinates(coordinates))
    );
  };

  /**
   * read address, send it to OpenStreetMap and move map/marker to new location
   */
  findAddress = (poiCollection, marker) => {
    let osm = this;
    let pacSearch = document.querySelector("#pac-search");

    // Prevent submitting the BE form on enter
    pacSearch.addEventListener("keydown", event => {
      if (event.keyCode === 13 && event.target.value) {
        fetch("https://nominatim.openstreetmap.org/search?q=" + encodeURI(event.target.value) + "&format=json&addressdetails=1", {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.length === 0) {
              alert("Address not found");
            } else {
              let lat = parseFloat(data[0].lat).toFixed(6);
              let lng = parseFloat(data[0].lon).toFixed(6);
              let address = data[0].address;
              let formattedAddress = osm.getFormattedAddress(address);

              switch (poiCollection.collectionType) {
                case "Point":
                  marker.setLatLng([lat, lng]);
                  osm.setLatLngFields(poiCollection, lat, lng, 0, formattedAddress);
                  break;
                case "Area":
                  osm.setLatLngFields(poiCollection, lat, lng, 0, formattedAddress);
                  break;
                case "Route":
                  osm.setLatLngFields(poiCollection, lat, lng, 0, formattedAddress);
                  break;
                case "Radius":
                  marker.setLatLng([lat, lng]);
                  marker.editor.updateResizeLatLng();
                  marker.editor.reset();
                  osm.setLatLngFields(poiCollection, lat, lng, marker.getRadius(), formattedAddress);
                  break;
              }

              osm.map.panTo([lat, lng]);
            }
          })
          .catch(error => console.error('Error:', error));

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
    let formattedAddress = "";
    let city = "";

    if (address.hasOwnProperty("road")) {
      formattedAddress += address.road;
    }
    if (address.hasOwnProperty("houseNumber")) {
      formattedAddress += " " + address.houseNumber;
    }
    if (address.hasOwnProperty("postcode")) {
      formattedAddress += ", " + address.postcode;
    }

    if (address.hasOwnProperty("village")) {
      city = address.village;
    }
    if (address.hasOwnProperty("town")) {
      city = address.town;
    }
    if (address.hasOwnProperty("city")) {
      city = address.city;
    }
    formattedAddress += " " + city;

    if (address.hasOwnProperty("country")) {
      formattedAddress += ", " + address.country;
    }

    return formattedAddress;
  };
}

export default new OpenStreetMapModule();
