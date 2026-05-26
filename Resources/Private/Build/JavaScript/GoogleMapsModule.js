import { ExtConf, PoiCollection } from '@jweiland/maps2/Classes.js';
import FormEngine from '@typo3/backend/form-engine.js';
import Notification from '@typo3/backend/notification.js';

class GoogleMapsModule {
  selector = '#maps2ConfigurationMap';
  record = [];
  extConf = [];
  marker = {};
  map = {};
  infoWindow = {};
  infoWindowContent = {};

  constructor() {
    const googleMaps = document.querySelector(this.selector);
    if (!googleMaps) {
      return;
    }
    const poiCollection = new PoiCollection(JSON.parse(googleMaps.dataset.poiCollection));
    const extConf = new ExtConf(JSON.parse(googleMaps.dataset.extConf));

    this.load(extConf).then(() => {
      this.initialize(googleMaps, poiCollection, extConf);
    });
  }

  load = (extConf) => {
    window._GoogleMapsModule = this;
    window._GoogleMapsModule.initMaps = this.initMaps;

    return new Promise(resolve => {
      this.resolve = resolve;
      const script = document.createElement("script");
      script.src = `${extConf.googleMapsLibrary}&callback=_GoogleMapsModule.initMaps&libraries=marker,places`;
      script.async = true;
      script.defer = true;
      document.body.append(script);
    });
  }

  initMaps = () => {
    if (this.resolve) {
      this.resolve();
    }
  };

  initialize = async (element, poiCollection, extConf) => {
    this.record = poiCollection;
    this.extConf = extConf;
    this.infoWindow = new google.maps.InfoWindow();
    this.infoWindowContent = document.getElementById("infowindow-content");

    const { Map } = await google.maps.importLibrary("maps");
    this.map = new Map(element, this.createMapOptions());

    if (extConf.googleMapsJavaScriptApiKey === "") {
      Notification.warning('Missing JS API Key', 'You have forgotten to set Google Maps JavaScript ApiKey in Extension Settings.', 15);
    }

    if (extConf.googleMapsGeocodeApiKey === "") {
      Notification.warning('Missing GeoCode API Key', 'You have forgotten to set Google Maps Geocode ApiKey in Extension Settings.', 15);
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
      this.map.setCenter({ lat: poiCollection.latitude, lng: poiCollection.longitude });
    } else {
      this.map.setCenter({ lat: extConf.defaultLatitude, lng: extConf.defaultLongitude });
    }

    const tabButton = document.querySelector("ul.t3js-tabs li:nth-of-type(2) button[data-bs-toggle='tab']");
    if (tabButton) {
      tabButton.addEventListener("shown.bs.tab", () => {
        google.maps.event.trigger(this.map, "resize");
        if (poiCollection.latitude && poiCollection.longitude) {
          this.map.setCenter({ lat: poiCollection.latitude, lng: poiCollection.longitude });
        } else {
          this.map.setCenter({ lat: extConf.defaultLatitude, lng: extConf.defaultLongitude });
        }
      });
    }
  };

  createMapOptions = () => ({
    zoom: 14,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    mapId: this.extConf.mapId
  });

  createCircleOptions = (map, record, extConf) => {
    return {
      map: map,
      center: { lat: record.latitude, lng: record.longitude },
      strokeColor: extConf.strokeColor,
      strokeOpacity: extConf.strokeOpacity,
      strokeWeight: extConf.strokeWeight,
      fillColor: extConf.fillColor,
      fillOpacity: extConf.fillOpacity,
      editable: true,
      radius: record.radius === 0 ? extConf.defaultRadius : record.radius
    };
  };

  createPolygonOptions = (paths, extConf) => ({
    paths: paths,
    strokeColor: extConf.strokeColor,
    strokeOpacity: extConf.strokeOpacity,
    strokeWeight: extConf.strokeWeight,
    fillColor: extConf.fillColor,
    fillOpacity: extConf.fillOpacity,
    editable: true
  });

  createPolylineOptions = (paths, extConf) => ({
    path: paths,
    strokeColor: extConf.strokeColor,
    strokeOpacity: extConf.strokeOpacity,
    strokeWeight: extConf.strokeWeight,
    editable: true
  });

  createMap = (element) => new google.maps.Map(element, this.createMapOptions());

  createMarker = async (record) => {
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    this.marker = new AdvancedMarkerElement({
      position: { lat: record.latitude, lng: record.longitude },
      map: this.map,
      gmpDraggable: true
    });

    this.infoWindow.setContent(this.infoWindowContent);

    this.marker.addListener("gmp-click", () => {
      this.infoWindow.open(this.map, this.marker);
    });

    this.marker.addListener('dragend', (event) => {
      const position = event.target.position;
      this.setLatLngFields(position.lat, position.lng, 0);
    });

    this.map.addListener('click', (event) => {
      this.marker.position = event.latLng;
      this.setLatLngFields(event.latLng.lat(), event.latLng.lng(), 0);
    });
  };

  createArea = (record) => {
    let coordinatesArray = [];
    if (record.configurationMap) {
      record.configurationMap.forEach(coord => {
        coordinatesArray.push({ lat: coord.latitude, lng: coord.longitude });
      });
    }
    if (coordinatesArray.length === 0) {
      coordinatesArray.push({ lat: record.latitude, lng: record.longitude });
    }

    const area = new google.maps.Polygon(this.createPolygonOptions(coordinatesArray, this.extConf));
    area.setMap(this.map);
    const path = area.getPath();

    ['set_at', 'insert_at'].forEach(eventName => {
      path.addListener(eventName, () => this.storeRouteAsJson(area));
    });

    area.addListener('rightclick', (event) => {
      if (event.vertex !== undefined) {
        path.removeAt(event.vertex);
        this.storeRouteAsJson(area);
      }
    });

    this.map.addListener('click', (event) => path.push(event.latLng));
    this.map.addListener('dragend', () => {
      const center = this.map.getCenter();
      this.setLatLngFields(center.lat(), center.lng(), 0);
    });
  };

  createRoute = (record) => {
    let coordinatesArray = [];
    if (record.configurationMap) {
      record.configurationMap.forEach(coord => {
        coordinatesArray.push({ lat: coord.latitude, lng: coord.longitude });
      });
    }
    if (coordinatesArray.length === 0) {
      coordinatesArray.push({ lat: record.latitude, lng: record.longitude });
    }

    const route = new google.maps.Polyline(this.createPolylineOptions(coordinatesArray, this.extConf));
    route.setMap(this.map);
    const path = route.getPath();

    ['set_at', 'insert_at'].forEach(eventName => {
      path.addListener(eventName, () => this.storeRouteAsJson(route));
    });

    route.addListener('rightclick', (event) => {
      if (event.vertex !== undefined) {
        path.removeAt(event.vertex);
        this.storeRouteAsJson(route);
      }
    });

    this.map.addListener('click', (event) => path.push(event.latLng));
    this.map.addListener('dragend', () => {
      const center = this.map.getCenter();
      this.setLatLngFields(center.lat(), center.lng(), 0);
    });
  };

  createRadius = (record) => {
    this.marker = new google.maps.Circle(this.createCircleOptions(this.map, record, this.extConf));

    this.marker.addListener('center_changed', () => {
      const center = this.marker.getCenter();
      this.setLatLngFields(center.lat(), center.lng(), this.marker.getRadius());
    });

    this.marker.addListener('radius_changed', () => {
      const center = this.marker.getCenter();
      this.setLatLngFields(center.lat(), center.lng(), this.marker.getRadius());
    });

    this.map.addListener('click', (event) => {
      this.marker.setCenter(event.latLng);
      this.setLatLngFields(event.latLng.lat(), event.latLng.lng(), this.marker.getRadius());
    });

    this.setLatLngFields(record.latitude, record.longitude, record.radius);
  };

  setLatLngFields = (lat, lng, rad, address) => {
    this.setFieldValue("latitude", Number(lat).toFixed(6));
    this.setFieldValue("longitude", Number(lng).toFixed(6));
    if (rad > 0) {
      this.setFieldValue("radius", Math.round(rad));
    }
    if (address) {
      this.setFieldValue("address", address);
    }
  };

  getUriForRoute = (route) => {
    const routeObject = {};
    route.getPath().getArray().forEach((latLng, index) => {
      routeObject[index] = latLng.toUrlValue();
    });
    return routeObject;
  };

  getFieldElement = (field) => FormEngine.getFieldElement(this.buildFieldName(field), '_list');

  buildFieldName = (field) => `data[tx_maps2_domain_model_poicollection][${this.record.uid}][${field}]`;

  setFieldValue = (field, value) => {
    const $fieldElement = this.getFieldElement(field);
    if ($fieldElement && $fieldElement.length) {
      const humanReadableField = $fieldElement.get(0);
      humanReadableField.value = value;
      humanReadableField.dispatchEvent(new Event('change'));
    }
  };

  storeRouteAsJson = (route) => {
    this.setFieldValue("configuration_map", JSON.stringify(this.getUriForRoute(route)));
  };

  findAddress = async () => {
    const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
    const pacInput = new PlaceAutocompleteElement({
        fields: ["place_id", "name", "formatted_address", "geometry"]
    });
    const geoCoder = new google.maps.Geocoder();

    this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(pacInput);

    pacInput.addEventListener("gmp-placechange", () => {
      this.infoWindow.close();
      const place = pacInput.place;

      if (!place.id || !place.geometry || !place.geometry.location) {
        return;
      }

      const location = place.geometry.location;
      const lat = location.lat();
      const lng = location.lng();
      const address = place.formatted_address;

      switch (this.record.collectionType) {
        case 'Point':
          this.marker.position = location;
          this.setLatLngFields(lat, lng, 0, address);
          break;
        case 'Area':
        case 'Route':
          this.setLatLngFields(lat, lng, 0, address);
          break;
        case 'Radius':
          this.marker.setCenter(location);
          this.setLatLngFields(lat, lng, this.marker.getRadius(), address);
          break;
      }

      this.map.setCenter(location);
      this.infoWindowContent.querySelector("#place-name").textContent = place.name;
      this.infoWindowContent.querySelector("#place-id").textContent = place.id;
      this.infoWindowContent.querySelector("#place-address").textContent = address;
      this.infoWindow.open(this.map, this.marker);
    });
  };
}

export default new GoogleMapsModule();
