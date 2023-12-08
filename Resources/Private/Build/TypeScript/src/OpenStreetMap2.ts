class OpenStreetMap2 {
  private allMarkers: any[];
  private categorizedMarkers: any;
  private bounds: L.LatLngBounds;
  private poiCollections: any;
  private editable: boolean;
  private map: L.Map;

  constructor(public element: HTMLElement, public environment: Environment) {
    this.allMarkers = [];
    this.categorizedMarkers = {};
    this.bounds = new L.LatLngBounds([
      [environment.extConf.defaultLatitude, environment.extConf.defaultLongitude]
    ]);
    this.editable = this.element.classList.contains("editMarker");

    this.preparePoisCollections();
    this.editable = this.element.classList.contains("editMarker");
    this.setWidthAndHeight();
    this.createMap();
    this.mainProcess();
  }

  private preparePoisCollections(): void {
    this.poiCollections = JSON.parse(this.element.getAttribute("data-pois") || '[]');
  }

  private mainProcess(): void {
    if (this.isPOICollectionsEmpty()) {
      this.createMarkerBasedOnDataAttributes();
    } else {
      this.createMarkerBasedOnPOICollections();
    }
  }

  private isPOICollectionsEmpty(): boolean {
    return this.poiCollections === "undefined" || this.poiCollections.length === 0;
  }

  private createMarkerBasedOnDataAttributes(): void {
    const latitude = this.getAttributeAsFloat("data-latitude");
    const longitude = this.getAttributeAsFloat("data-longitude");

    if (!isNaN(latitude) && !isNaN(longitude)) {
      this.createMarkerByLatLng(latitude, longitude);
    }
  }

  private getAttributeAsFloat(attributeName: string): number {
    return parseFloat(this.element.getAttribute(attributeName) || "");
  }

  private createMarkerBasedOnPOICollections(): void {
    this.createPointByCollectionType(this.environment);
    if (this.countObjectProperties(this.categorizedMarkers) > 1) {
      this.showSwitchableCategories(this.environment);
    }
    this.adjustMapZoom();
  }

  private adjustMapZoom(): void {
    if (this.shouldFitBounds()) {
      this.map.fitBounds(this.bounds);
    } else {
      this.map.panTo([this.poiCollections[0].latitude, this.poiCollections[0].longitude]);
    }
  }

  private shouldFitBounds(): boolean {
    return this.environment.settings.forceZoom === false
      && (this.poiCollections.length > 1
        || (this.poiCollections.length === 1
          && (this.poiCollections[0].collectionType === "Area"
            || this.poiCollections[0].collectionType === "Route")));
  }

  private setWidthAndHeight (): void {
    this.element.style.height = this.normalizeDimension(this.environment.settings.mapHeight);
    this.element.style.width = this.normalizeDimension(this.environment.settings.mapWidth);
  }

  private normalizeDimension (dimension: string | number): string {
    let normalizedDimension = String(dimension);
    if (this.canBeInterpretedAsNumber(normalizedDimension)) {
      normalizedDimension += 'px';
    }
    return normalizedDimension;
  }

  private createMap(): void {
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

  private groupCategories(environment: any): any {
    let me = this;
    let groupedCategories: any = {};
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
  }

  private getCategoriesOfCheckboxesWithStatus(form: HTMLElement, isChecked: boolean): number[] {
    let categories: number[] = [];
    let checkboxes = isChecked ? form.querySelectorAll("input:checked") : form.querySelectorAll("input:not(:checked)");
    checkboxes.forEach((checkbox) => {
      categories.push(parseInt((checkbox as HTMLInputElement).value));
    });

    return categories;
  }

  private getMarkersToChangeVisibilityFor(categoryUid: string, form: HTMLElement, isChecked: boolean): any[] {
    let markers: any[] = [];
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

  private showSwitchableCategories(environment: any): void {
    let categories = this.groupCategories(environment);
    let form = document.createElement("form");
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
        let isChecked = (checkbox as HTMLInputElement).checked;
        let categoryUid = (checkbox as HTMLInputElement).value;
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

  private getCheckbox(category: any): HTMLElement {
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

  private countObjectProperties(obj: any): number {
    let count = 0;
    for (let key in obj) {
      if (obj.hasOwnProperty(key)) {
        count++;
      }
    }
    return count;
  }

  private createPointByCollectionType(environment: any): void {
    let marker;
    let categoryUid = 0;

    for (let i = 0; i < this.poiCollections.length; i++) {
      if (this.poiCollections[i].strokeColor === "") {
        this.poiCollections[i].strokeColor = environment.extConf.strokeColor;
      }
      if (this.poiCollections[i].strokeOpacity === "") {
        this.poiCollections[i].strokeOpacity = environment.extConf.strokeOpacity;
      }
      // ... (similar updates for other properties)

      marker = null;
      switch (this.poiCollections[i].collectionType) {
        case "Point":
          marker = this.createMarker(this.poiCollections[i], environment);
          break;
        case "Area":
          marker = this.createArea(this.poiCollections[i], environment);
          break;
        case "Route":
          marker = this.createRoute(this.poiCollections[i], environment);
          break;
        case "Radius":
          marker = this.createRadius(this.poiCollections[i], environment);
          break;
      }

      this.allMarkers.push({
        marker: marker,
        poiCollection: this.poiCollections[i]
      });

      categoryUid = 0;
      for (let c = 0; c < this.poiCollections[i].categories.length; c++) {
        categoryUid = this.poiCollections[i].categories[c].uid;
        if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
          this.categorizedMarkers[categoryUid] = [];
        }
        this.categorizedMarkers[categoryUid].push(marker);
      }
    }
  }

  private createMarkerByLatLng(latitude: number, longitude: number): void {
    let marker = L.marker(
      [latitude, longitude]
    ).addTo(this.map);

    this.bounds.extend(marker.getLatLng());
  }

  private inList(list: string, item: string): number {
    let catSearch = ',' + list + ',';
    item = ',' + item + ',';
    return catSearch.indexOf(item);
  }

  private createMarker(poiCollection: any, environment: any): any {
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
      this.addEditListeners(this.element, marker, poiCollection, environment);
    } else {
      this.addInfoWindow(marker, poiCollection, environment);
    }

    return marker;
  }

  private createArea(poiCollection: any, environment: any): any {
    let latlngs = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
      let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
      this.bounds.extend(latLng);
      latlngs.push(latLng);
    }

    let marker = L.polygon(latlngs, {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    }).addTo(this.map);

    this.addInfoWindow(marker, poiCollection, environment);

    return marker;
  }

  private createRoute(poiCollection: any, environment: any): any {
    let latlngs = [];
    for (let i = 0; i < poiCollection.pois.length; i++) {
      let latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
      this.bounds.extend(latLng);
      latlngs.push(latLng);
    }

    let marker = L.polyline(latlngs, {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity
    }).addTo(this.map);

    this.addInfoWindow(marker, poiCollection, environment);

    return marker;
  }

  private createRadius(poiCollection: any, environment: any): any {
    let marker = L.circle([poiCollection.latitude, poiCollection.longitude], {
      color: poiCollection.strokeColor,
      opacity: poiCollection.strokeOpacity,
      weight: poiCollection.strokeWeight,
      fillColor: poiCollection.fillColor,
      fillOpacity: poiCollection.fillOpacity,
      radius: poiCollection.radius
    }).addTo(this.map);

    this.bounds.extend(marker.getBounds());

    this.addInfoWindow(marker, poiCollection, environment);

    return marker;
  }

  private addInfoWindow(element: any, poiCollection: any, environment: Environment): void {
    element.addEventListener("click", () => {
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
          element.bindPopup(data.content).openPopup();
        })
        .catch(error => console.error('Error:', error));
    });
  }

  private addEditListeners(mapContainer: HTMLElement, marker: any, poiCollection: any, environment: any): void {
    marker.on('dragend', () => {
      let lat = marker.getLatLng().lat.toFixed(6);
      let lng = marker.getLatLng().lng.toFixed(6);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.latitude-${environment.contentRecord.uid}`)
        .setAttribute("value", lat);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.longitude-${environment.contentRecord.uid}`)
        .setAttribute("value", lng);
    });

    this.map.on('click', (event: L.LeafletMouseEvent) => {
      marker.setLatLng(event.latlng);
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.latitude-${environment.contentRecord.uid}`)
        .setAttribute("value", event.latlng.lat.toFixed(6));
      mapContainer
        .previousElementSibling
        ?.querySelector(`input.longitude-${environment.contentRecord.uid}`)
        .setAttribute("value", event.latlng.lng.toFixed(6));
    });
  }

  private canBeInterpretedAsNumber(value: string|number): boolean {
    return typeof value === 'number' || !isNaN(Number(value));
  }

  private getExtConf(): ExtConf {
    return this.environment.extConf;
  }

  private getSettings(): Settings {
    return this.environment.settings;
  }
}

let maps2OpenStreetMaps: OpenStreetMap2[] = [];

document.querySelectorAll(".maps2").forEach((element: HTMLElement) => {
  const environment = typeof element.dataset.environment !== 'undefined' ? element.dataset.environment : '{}';
  const override = typeof element.dataset.override !== 'undefined' ? element.dataset.override : '{}';

  maps2OpenStreetMaps.push(new OpenStreetMap2(
    element,
    {...JSON.parse(environment), ...JSON.parse(override)}
  ));
});
