class OpenStreetMap2 {
  element = {};
  environment = {};
  editable = false;;
  bounds = {};

  allMarkers = [];
  categorizedMarkers = {};
  poiCollections = [];
  map = {};

  constructor(element, environment) {
    this.element = element;
    this.environment = environment;
    this.editable = this.element.classList.contains("editMarker");
    this.bounds = new L.LatLngBounds();

    this.preparePoiCollection();
    this.setMapDimensions();
    this.createMap();
    this.setMarkersOnMap();
  }

  preparePoiCollection() {
    this.poiCollections = JSON.parse(this.element.getAttribute("data-pois") || '[]');
  }

  setMarkersOnMap() {
    if (this.isPOICollectionsEmpty()) {
      this.createMarkerBasedOnDataAttributes();
    } else {
      this.createMarkerBasedOnPOICollections();
    }
  }

  /**
   * @returns {boolean}
   */
  isPOICollectionsEmpty() {
    return this.poiCollections.length === 0;
  }

  createMarkerBasedOnDataAttributes() {
    const latitude = this.getAttributeAsFloat("data-latitude");
    const longitude = this.getAttributeAsFloat("data-longitude");

    if (!isNaN(latitude) && !isNaN(longitude)) {
      this.createMarkerByLatLng(latitude, longitude);
    }
  }

  /**
   * @param {string} attributeName
   * @returns {number}
   */
  getAttributeAsFloat(attributeName) {
    return parseFloat(this.element.getAttribute(attributeName) || "");
  }

  createMarkerBasedOnPOICollections() {
    this.createPointByCollectionType();
    if (this.countObjectProperties(this.categorizedMarkers) > 1) {
      this.showSwitchableCategories();
    }
    this.adjustMapZoom();
  }

  adjustMapZoom() {
    if (this.shouldFitBounds()) {
      this.map.fitBounds(this.bounds);
    } else {
      this.map.panTo([this.poiCollections[0].latitude, this.poiCollections[0].longitude]);
    }
  }

  /**
   * @returns {boolean}
   */
  shouldFitBounds() {
    if (this.getSettings().forceZoom === true) {
      return false;
    }

    if (this.poiCollections === null) {
      return false;
    }

    if (this.poiCollections.length > 1) {
      return true;
    }

    if (
      this.poiCollections.length === 1
      && (
        this.poiCollections[0].collectionType === "Area"
        || this.poiCollections[0].collectionType === "Route"
      )
    ) {
      return true;
    }

    return false;
  }

  setMapDimensions() {
    this.element.style.height = this.normalizeDimension(this.getSettings().mapHeight);
    this.element.style.width = this.normalizeDimension(this.getSettings().mapWidth);
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

  createMap() {
    this.map = L.map(
      this.element, {
        center: [this.getExtConf().defaultLatitude, this.getExtConf().defaultLongitude],
        zoom: this.getSettings().zoom ? this.getSettings().zoom : 12,
        editable: this.editable,
        scrollWheelZoom: this.getSettings().activateScrollWheel !== "0"
      }
    );

    L.tileLayer(this.getSettings().mapTile, {
      attribution: this.getSettings().mapTileAttribution,
      maxZoom: 20
    }).addTo(this.map);
  }

  /**
   * @returns {{[p: string]: Category}}
   */
  groupCategories() {
    const groupedCategories = {};

    this.poiCollections.forEach((poiCollection) => {
      const categoryUids = poiCollection.categories.map((category) => String(category.uid));

      categoryUids
        .filter((categoryUid) => this.getSettings().categories.includes(categoryUid))
        .forEach((categoryUid) => {
          if (!groupedCategories.hasOwnProperty(categoryUid)) {
            groupedCategories[categoryUid] = poiCollection.categories.find((category) => String(category.uid) === categoryUid);
          }
        });
    });

    return groupedCategories;
  }

  /**
   * @param {HTMLElement} form
   * @param {boolean} isChecked
   * @returns {number[]}
   */
  getCategoriesOfCheckboxesWithStatus(form, isChecked) {
    let categories = [];
    let checkboxes = isChecked
      ? Array.from(form.querySelectorAll("input:checked"))
      : Array.from(form.querySelectorAll("input:not(:checked)"));

    checkboxes.forEach((checkbox) => {
      categories.push(parseInt((checkbox).value));
    });

    return categories;
  }

  /**
   * @param {string} categoryUid
   * @param {HTMLElement} form
   * @param { boolean} isChecked
   * @returns {*[]}
   */
  getMarkersToChangeVisibilityFor(categoryUid, form, isChecked) {
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

  showSwitchableCategories() {
    let categories = this.groupCategories();
    let form = document.createElement("form");
    form.classList.add("txMaps2Form");
    form.setAttribute("id", "txMaps2Form-" + this.getContentRecord().uid);

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
          if (isChecked) {
            this.map.addLayer(marker);
          } else {
            this.map.removeLayer(marker);
          }
        });
      });
    });

    this.element.insertAdjacentElement("afterend", form);
  }

  /**
   * @param {Category} category
   * @returns {HTMLElement}
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
   * @param {object} obj
   * @returns {number}
   */
  countObjectProperties(obj) {
    let count = 0;
    for (let key in obj) {
      if (obj.hasOwnProperty(key)) {
        count++;
      }
    }
    return count;
  }

  createPointByCollectionType() {
    let marker;
    let categoryUid = 0;

    if (this.poiCollections !== null && this.poiCollections.length) {
      this.poiCollections.forEach(poiCollection => {
        if (poiCollection.strokeColor === "") {
          poiCollection.strokeColor = this.getExtConf().strokeColor;
        }
        if (poiCollection.strokeOpacity === "") {
          poiCollection.strokeOpacity = this.getExtConf().strokeOpacity;
        }
        if (poiCollection.strokeWeight === "") {
          poiCollection.strokeWeight = this.getExtConf().strokeWeight;
        }
        if (poiCollection.fillColor === "") {
          poiCollection.fillColor = this.getExtConf().fillColor;
        }
        if (poiCollection.fillOpacity === "") {
          poiCollection.fillOpacity = this.getExtConf().fillOpacity;
        }

        marker = null;
        switch (poiCollection.collectionType) {
          case "Point":
            marker = this.createMarker(poiCollection);
            break;
          case "Area":
            marker = this.createArea(poiCollection);
            break;
          case "Route":
            marker = this.createRoute(poiCollection);
            break;
          case "Radius":
            marker = this.createRadius(poiCollection);
            break;
        }

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
          this.categorizedMarkers[categoryUid].push(marker);
        }
      });
    }
  }

  /**
   * @param {number} latitude
   * @param {number} longitude
   */
  createMarkerByLatLng(latitude, longitude) {
    let marker = L.marker(
      [latitude, longitude]
    ).addTo(this.map);

    this.bounds.extend(marker.getLatLng());
  }

  /**
   * @param {PoiCollection} poiCollection
   * @returns {Marker<any>}
   */
  createMarker(poiCollection) {
    let marker = L.marker(
      [poiCollection.latitude, poiCollection.longitude],
      {
        'draggable': this.editable
      }
    ).addTo(this.map);

    if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
      let icon = L.icon({
        iconUrl: poiCollection.markerIcon,
        iconSize: [poiCollection.markerIconWidth, poiCollection.markerIconHeight],
        iconAnchor: [poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY]
      });
      marker.setIcon(icon);
    }

    this.bounds.extend(marker.getLatLng());

    if (this.editable) {
      this.addEditListeners(this.element, marker, poiCollection);
    } else {
      this.addInfoWindow(marker, poiCollection);
    }

    return marker;
  }

  /**
   * @param {PoiCollection} poiCollection
   * @returns {Polygon<any>}
   */
  createArea(poiCollection) {
    let latlngs = [];

    poiCollection.pois.forEach(poi => {
      let latLng = [poi.latitude, poi.longitude];
      this.bounds.extend(latLng);
      latlngs.push(latLng);
    });

    let marker = L.polygon(latlngs, {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    }).addTo(this.map);

    this.addInfoWindow(marker, poiCollection);

    return marker;
  }

  /**
   * @param {PoiCollection} poiCollection
   * @returns {Polyline<LineString | MultiLineString, any>}
   */
  createRoute(poiCollection) {
    let latlngs = [];

    poiCollection.pois.forEach(poi => {
      let latLng = [poi.latitude, poi.longitude];
      this.bounds.extend(latLng);
      latlngs.push(latLng);
    });

    let marker = L.polyline(latlngs, {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    }).addTo(this.map);

    this.addInfoWindow(marker, poiCollection);

    return marker;
  }

  /**
   * @param {PoiCollection} poiCollection
   * @returns {Circle<any>}
   */
  createRadius(poiCollection) {
    let marker = L.circle([poiCollection.latitude, poiCollection.longitude], {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity,
      radius: poiCollection.radius
    }).addTo(this.map);

    this.bounds.extend(marker.getBounds());

    this.addInfoWindow(marker, poiCollection);

    return marker;
  }

  /**
   * @param {HTMLElement} element
   * @param {PoiCollection} poiCollection
   */
  addInfoWindow(element, poiCollection) {
    element.addEventListener("click", () => {
      fetch(this.environment.ajaxUrl, {
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
          element.bindPopup(data.content).openPopup();
        })
        .catch(error => console.error('Error:', error));
    });
  }

  /**
   * @param {HTMLElement} mapContainer
   * @param {L.Marker} marker
   * @param {PoiCollection} poiCollection
   */
  addEditListeners(mapContainer, marker, poiCollection) {
    marker.on('dragend', () => {
      let lat = marker.getLatLng().lat.toFixed(6);
      let lng = marker.getLatLng().lng.toFixed(6);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.latitude-${this.getContentRecord().uid}`)
        .setAttribute("value", lat);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.longitude-${this.getContentRecord().uid}`)
        .setAttribute("value", lng);
    });

    this.map.on('click', (event) => {
      marker.setLatLng(event.latlng);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.latitude-${this.getContentRecord().uid}`)
        .setAttribute("value", event.latlng.lat.toFixed(6));
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.longitude-${this.getContentRecord().uid}`)
        .setAttribute("value", event.latlng.lng.toFixed(6));
    });
  }

  /**
   * return {boolean}
   */
  canBeInterpretedAsNumber(value) {
    return typeof value === 'number' || !isNaN(Number(value));
  }

  /**
   * return {ContentRecord}
   */
  getContentRecord() {
    return this.environment.contentRecord;
  }

  /**
   * return {ExtConf}
   */
  getExtConf() {
    return this.environment.extConf;
  }

  /**
   * return {Settings}
   */
  getSettings() {
    return this.environment.settings;
  }
}

let maps2OpenStreetMaps = [];

document.querySelectorAll(".maps2").forEach((element) => {
  const environment = typeof element.dataset.environment !== 'undefined' ? element.dataset.environment : '{}';
  const override = typeof element.dataset.override !== 'undefined' ? element.dataset.override : '{}';

  // Pass in the objects to merge as arguments.
  // For a deep extend, set the first argument to `true`.
  const extend = () => {
    let extended = {};
    let deep = false;
    let i = 0;
    let length = arguments.length;

    // Check for deep merge
    if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
      deep = arguments[0];
      i++;
    }

    // Merge the object into the extended object
    const merge = function (obj) {
      for ( var prop in obj ) {
        if ( Object.prototype.hasOwnProperty.call( obj, prop ) ) {
          // If deep merge and property is an object, merge properties
          if ( deep && Object.prototype.toString.call(obj[prop]) === '[object Object]' ) {
            extended[prop] = extend( true, extended[prop], obj[prop] );
          } else {
            extended[prop] = obj[prop];
          }
        }
      }
    };

    // Loop through each object and conduct a merge
    for ( ; i < length; i++ ) {
      var obj = arguments[i];
      merge(obj);
    }

    return extended;
  };

  maps2OpenStreetMaps.push(new OpenStreetMap2(
    element,
    extend(true, JSON.parse(environment), JSON.parse(override))
  ));
});
