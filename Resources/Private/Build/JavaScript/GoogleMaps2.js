class GoogleMaps2 {
  allMarkers = [];
  categorizedMarkers = {};
  pointMarkers = [];
  bounds = {};
  infoWindow = {};
  poiCollections = {};
  editable = {};
  map = {};

  /**
   * @param {HTMLElement} element
   * @param {Environment} environment
   * @constructor
   */
  constructor (element, environment) {
    this.allMarkers = [];
    this.categorizedMarkers = {};
    this.pointMarkers = [];
    this.bounds = new google.maps.LatLngBounds();
    this.infoWindow = new google.maps.InfoWindow();
    this.poiCollections = JSON.parse(element.dataset.pois);
    this.editable = element.classList.contains('editMarker');

    this.setMapDimensions(element, environment.settings);

    this.createMap(element, environment);

    if (typeof this.poiCollections === 'undefined') {
      // Plugin: CityMap
      let lat = Number(element.dataset.latitude);
      let lng = Number(element.dataset.longitude);
      if (lat && lng) {
        this.createMarkerByLatLng(lat, lng);
        this.map.setCenter(new google.maps.LatLng(lat, lng));
      } else {
        // Fallback
        this.map.setCenter(new google.maps.LatLng(environment.extConf.defaultLatitude, environment.extConf.defaultLongitude));
      }
    } else {
      // normal case
      this.createPointByCollectionType(element, environment);
      if (
        typeof environment.settings.markerClusterer !== 'undefined'
        && environment.settings.markerClusterer.enable === 1
      ) {
        new MarkerClusterer(
          this.map,
          this.pointMarkers,
          { imagePath: environment.settings.markerClusterer.imagePath }
        );
      }
      if (this.countObjectProperties(this.categorizedMarkers) > 1) {
        this.showSwitchableCategories(element, environment);
      }
      if (
        environment.settings.forceZoom === false
        && (
          this.poiCollections.length > 1
          || (
            this.poiCollections.length === 1
            && (
              this.poiCollections[0].collectionType === 'Area'
              || this.poiCollections[0].collectionType === 'Route'
            )
          )
        )
      ) {
        this.map.fitBounds(this.bounds);
      } else {
        this.map.setCenter(new google.maps.LatLng(this.poiCollections[0].latitude, this.poiCollections[0].longitude));
      }
    }
  }

  /**
   * Return a MapOptions object which can be assigned to the Map object of Google
   *
   * @param {Settings} settings
   * @return {object}
   */
  getMapOptions = settings => {
    let mapOptions = {
      mapTypeId: '',
      zoom: parseInt(settings.zoom),
      zoomControl: (parseInt(settings.zoomControl) !== 0),
      mapTypeControl: (parseInt(settings.mapTypeControl) !== 0),
      scaleControl: (parseInt(settings.scaleControl) !== 0),
      streetViewControl: (parseInt(settings.streetViewControl) !== 0),
      fullscreenControl: (parseInt(settings.fullScreenControl) !== 0),
      scrollwheel: settings.activateScrollWheel,
      styles: ''
    };

    if (settings.styles) {
      mapOptions.styles = eval(settings.styles);
    }

    switch (settings.mapTypeId) {
      case 'google.maps.MapTypeId.HYBRID':
      case 'hybrid':
        mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
        break;
      case 'google.maps.MapTypeId.ROADMAP':
      case 'roadmap':
        mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
        break;
      case 'google.maps.MapTypeId.SATELLITE':
      case 'satellite':
        mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
        break;
      case 'google.maps.MapTypeId.TERRAIN':
      case 'terrain':
        mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
        break;
    }

    return mapOptions;
  }

  /**
   * Returns CircleOptions which can be assigned to the Circle object of Google
   *
   * @param {L.Map} map
   * @param {object} centerPosition
   * @param {PoiCollection} poiCollection
   * @return {object}
   */
  getCircleOptions (map, centerPosition, poiCollection) {
    return {
      map: map,
      center: centerPosition,
      radius: poiCollection.radius,
      strokeColor: poiCollection.strokeColor,
      strokeOpacity: poiCollection.strokeOpacity,
      strokeWeight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    };
  }

  /**
   * Returns PolygonOptions which can be assigned to the Polygon object of Google
   *
   * @param {object} paths
   * @param {PoiCollection} poiCollection
   * @return {object}
   */
  getPolygonOptions (paths, poiCollection) {
    return {
      paths: paths,
      strokeColor: poiCollection.strokeColor,
      strokeOpacity: poiCollection.strokeOpacity,
      strokeWeight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    };
  }

  /**
   * Return PolylineOptions which can be assigned to the Polyline object of Google
   *
   * @param {object} paths
   * @param {PoiCollection} poiCollection
   * @return {object}
   */
  getPolylineOptions (paths, poiCollection) {
    return {
      path: paths,
      strokeColor: poiCollection.strokeColor,
      strokeOpacity: poiCollection.strokeOpacity,
      strokeWeight: poiCollection.strokeWeight,
    };
  }

  /**
   * Create Map
   *
   * @param {HTMLElement} element
   * @param {Environment} environment
   */
  createMap (element, environment) {
    this.map = new google.maps.Map(
      element,
      this.getMapOptions(environment.settings)
    );
  }

  /**
   * @param {string | number} value
   * @return {boolean}
   */
  canBeInterpretedAsNumber(value) {
    return typeof value === 'number' || !isNaN(Number(value));
  }

  /**
   * @param {string | number} dimension
   * @returns {string}
   */
  normalizeDimension(dimension) {
    let normalizedDimension = String(dimension);

    if (this.canBeInterpretedAsNumber(normalizedDimension)) {
      normalizedDimension += 'px';
    }

    return normalizedDimension;
  }

  /**
   * @param {HTMLElement} element
   * @param {Settings} settings
   */
  setMapDimensions(element, settings) {
    element.style.height = this.normalizeDimension(settings.mapHeight);
    element.style.width = this.normalizeDimension(settings.mapWidth);
  }

  /**
   * Group Categories
   *
   * @param {Environment} environment
   */
  groupCategories = environment => {
    let groupedCategories = {};
    let categoryUid = "0";
    for (let x = 0; x < this.poiCollections.length; x++) {
      for (let y = 0; y < this.poiCollections[x].categories.length; y++) {
        categoryUid = String(this.poiCollections[x].categories[y].uid);
        if (this.inList(environment.settings.categories, categoryUid) > -1 && !groupedCategories.hasOwnProperty(categoryUid)) {
          groupedCategories[categoryUid] = this.poiCollections[x].categories[y];
        }
      }
    }

    return groupedCategories;
  };

  /**
   * Get categories of all checkboxes with a given status
   *
   * @param {HTMLElement} form The HTML form element containing the checkboxes
   * @param {boolean} isChecked Get checkboxes of this status only
   */
  getCategoriesOfCheckboxesWithStatus = (form, isChecked) => {
    let categories = [];
    let checkboxes = isChecked ? form.querySelectorAll("input:checked") : form.querySelectorAll("input:not(input:checked)");

    checkboxes.forEach(checkbox => {
      categories.push(parseInt(checkbox.value));
    });

    return categories;
  }

  getMarkersToChangeVisibilityFor = (categoryUid, form, isChecked) => {
    let markers = [];
    if (this.allMarkers.length === 0) {
      return markers;
    }

    let marker = null;
    let allCategoriesOfMarker = null;
    let categoriesOfCheckboxesWithStatus = this.getCategoriesOfCheckboxesWithStatus(form, isChecked);
    for (let i = 0; i < this.allMarkers.length; i++) {
      marker = this.allMarkers[i];
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
   * @param {HTMLElement} element
   * @param {Environment} environment
   */
  showSwitchableCategories = (element, environment) => {
    let categories = this.groupCategories(environment);
    let form = document.createElement("form");
    let span = {};

    form.classList.add("txMaps2Form");
    form.setAttribute("id", "txMaps2Form-" + environment.contentRecord.uid);

    // Add checkbox for category
    for (let categoryUid in categories) {
      if (categories.hasOwnProperty(categoryUid)) {
        form.appendChild(this.getCheckbox(categories[categoryUid]));
        form.querySelector("#checkCategory_" + categoryUid)?.insertAdjacentHTML(
          "afterend",
          `<span class="map-category">${categories[categoryUid].title}</span>`
        );
      }
    }

    // Add listener for checkboxes
    form.querySelectorAll("input").forEach((checkbox) => {
      checkbox.addEventListener("click", () => {
        let isChecked = (checkbox).checked;
        let categoryUid = (checkbox).value;
        let markers = this.getMarkersToChangeVisibilityFor(categoryUid, form, isChecked);

        markers.forEach((marker) => {
          marker.setVisible(isChecked);
        });
      });
    });

    element.insertAdjacentElement("afterend", form);
  }

  /**
   * Get Checkbox for Category
   *
   * @param category
   */
  getCheckbox(category) {
    let div = document.createElement("div");
    div.classList.add("form-group");
    div.innerHTML = `
      <div class="checkbox">
          <label>
              <input type="checkbox" class="checkCategory" id="checkCategory_${category.uid}" checked="checked" value="${category.uid}">
          </label>
      </div>`;

    return div;
  }

  /**
   * Count Object properties
   *
   * @param obj
   */
  countObjectProperties = obj => {
    let count = 0;
    for (let key in obj) {
      if (obj.hasOwnProperty(key)) {
        count++;
      }
    }
    return count;
  }

  /**
   * Create Point by CollectionType
   *
   * @param {HTMLElement} element
   * @param {Environment} environment
   */
  createPointByCollectionType = (element, environment) => {
    let marker;
    let categoryUid = 0;

    this.poiCollections.forEach(poiCollection => {
      if (poiCollection.strokeColor === "") {
        poiCollection.strokeColor = environment.extConf.strokeColor;
      }
      if (poiCollection.strokeOpacity === "") {
        poiCollection.strokeOpacity = environment.extConf.strokeOpacity;
      }
      if (poiCollection.strokeWeight === "") {
        poiCollection.strokeWeight = environment.extConf.strokeWeight;
      }
      if (poiCollection.fillColor === "") {
        poiCollection.fillColor = environment.extConf.fillColor;
      }
      if (poiCollection.fillOpacity === "") {
        poiCollection.fillOpacity = environment.extConf.fillOpacity;
      }

      marker = null;
      switch (poiCollection.collectionType) {
        case "Point":
          marker = this.createMarker(poiCollection, element, environment);
          break;
        case "Area":
          marker = this.createArea(poiCollection, environment);
          break;
        case "Route":
          marker = this.createRoute(poiCollection, environment);
          break;
        case "Radius":
          marker = this.createRadius(poiCollection, environment);
          break;
      }

      if (marker !== null) {
        this.allMarkers.push({
          marker: marker,
          poiCollection: poiCollection
        });

        categoryUid = 0;
        for (let c = 0; c < poiCollection.categories.length; c++) {
          categoryUid = poiCollection.categories[c].uid;
          if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
            this.categorizedMarkers[categoryUid] = [];
          }
          this.categorizedMarkers[categoryUid].push({
            marker: marker,
            relatedCategories: poiCollection.categories
          });
        }
      }
    });
  }

  /**
   * Create Marker with InfoWindow
   *
   * @param {PoiCollection} poiCollection
   * @param {HTMLElement} element
   * @param {Environment} environment
   */
  createMarker = (poiCollection, element, environment) => {
    let categoryUid = "0";
    let marker = new google.maps.Marker({
      position: new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
      map: this.map
    });
    marker.setDraggable(this.editable);

    // assign first found marker icon, if available
    if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
      let icon = {
        url: poiCollection.markerIcon,
        scaledSize: new google.maps.Size(poiCollection.markerIconWidth, poiCollection.markerIconHeight),
        anchor: new google.maps.Point(poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY)
      };
      marker.setIcon(icon);
    }

    this.pointMarkers.push(marker);
    this.bounds.extend(marker.position);

    if (this.editable) {
      this.addEditListeners(element, marker, poiCollection, environment);
    } else {
      this.addInfoWindow(marker, poiCollection, environment);
    }

    return marker;
  }

  /**
   * Create Area
   *
   * @param poiCollection
   * @param environment
   */
  createArea = (poiCollection, environment) => {
    let latLng;
    let paths = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
      latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
      this.bounds.extend(latLng);
      paths.push(latLng);
    }

    if (paths.length === 0) {
      paths.push(this.mapPosition);
    }

    let area = new google.maps.Polygon(this.getPolygonOptions(paths, poiCollection));
    area.setMap(this.map);
    this.addInfoWindow(area, poiCollection, environment);

    return area;
  }

  /**
   * Create Route
   *
   * @param poiCollection
   * @param environment
   */
  createRoute = (poiCollection, environment) => {
    let latLng;
    let paths = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
      latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
      this.bounds.extend(latLng);
      paths.push(latLng);
    }

    if (paths.length === 0) {
      paths.push(this.mapPosition);
    }

    let route = new google.maps.Polyline(this.getPolylineOptions(paths, poiCollection));
    route.setMap(this.map);
    this.addInfoWindow(route, poiCollection, environment);

    return route;
  }

  /**
   * Create Radius
   *
   * @param poiCollection
   * @param environment
   */
  createRadius = (poiCollection, environment) => {
    let circle = new google.maps.Circle(
      this.getCircleOptions(
        this.map,
        new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
        poiCollection
      )
    );

    this.bounds.union(circle.getBounds());
    this.addInfoWindow(circle, poiCollection, environment);

    return circle;
  }

  /**
   * Add Info Window to element
   *
   * @param element
   * @param poiCollection
   * @param environment
   */
  addInfoWindow = (element, poiCollection, environment) => {
    // we need these both vars to be set global. So that we can access them in Listener
    let infoWindow = this.infoWindow;
    let map = this.map;
    google.maps.event.addListener(element, "click", event => {
      fetch(environment.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          poiCollection: poiCollection.uid
        })
      })
        .then(response => response.json())
        .then(data => {
          infoWindow.close();
          infoWindow.setContent(data.content);

          // Do not set pointer of InfoWindow to the same pointer of the POI icon.
          // In case of Point the pointer of InfoWindow should be at mouse position.
          if (poiCollection.collectionType === "Point") {
            infoWindow.setPosition(null);
            infoWindow.open(map, element);
          } else {
            infoWindow.setPosition(new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude));
            infoWindow.open(map);
          }
        })
        .catch(error => console.error('Error:', error));
    });
  }

  /**
   * Check for item in list
   * Check if an item exists in a comma-separated list of items.
   *
   * @param list
   * @param item
   */
  inList = (list, item) => {
    let catSearch = ',' + list + ',';
    item = ',' + item + ',';
    return catSearch.search(item);
  };

  /**
   * Create Marker with InfoWindow
   *
   * @param latitude
   * @param longitude
   */
  createMarkerByLatLng = (latitude, longitude) => {
    let marker = new google.maps.Marker({
      position: new google.maps.LatLng(latitude, longitude),
      map: this.map
    });
    this.bounds.extend(marker.position);
  };

  /**
   * Add Edit Listeners
   * This will only work for Markers (Point)
   *
   * @param mapContainer
   * @param marker
   * @param poiCollection
   * @param environment
   */
  addEditListeners = (mapContainer, marker, poiCollection, environment) => {
    // update fields and marker while dragging
    google.maps.event.addListener(marker, 'dragend', () => {
      let lat = marker.getPosition().lat().toFixed(6);
      let lng = marker.getPosition().lng().toFixed(6);
      mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(lat);
      mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(lng);
    });

    // update fields and marker when clicking on the map
    google.maps.event.addListener(this.map, 'click', event => {
      marker.setPosition(event.latLng);
      mapContainer.prevAll("input.latitude-" + environment.contentRecord.uid).val(event.latLng.lat().toFixed(6));
      mapContainer.prevAll("input.longitude-" + environment.contentRecord.uid).val(event.latLng.lng().toFixed(6));
    });
  };
}

let maps2GoogleMaps = [];

/**
 * This function will be called by the &callback argument of the Google Maps API library
 */
function initMap () {
  document.querySelectorAll(".maps2").forEach(element => {
    const environment = typeof element.dataset.environment !== 'undefined' ? element.dataset.environment : '{}';
    const override = typeof element.dataset.override !== 'undefined' ? element.dataset.override : '{}';

    maps2GoogleMaps.push(new GoogleMaps2(
      element,
      {...JSON.parse(environment), ...JSON.parse(override)}
    ));
  });

  // Initialize radius search
  let address = document.querySelector('#maps2Address');
  let radius = document.querySelector('#maps2Radius');
  if (address !== null && radius !== null) {
    let autocomplete = new google.maps.places.Autocomplete(address, {
      fields: [
        "place_id"
      ]
    });

    address.addEventListener("keydown", event => {
      if (event.keyCode === 13) return false;
    });
  }
}
