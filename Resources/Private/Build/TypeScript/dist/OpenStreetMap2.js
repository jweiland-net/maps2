class OpenStreetMap2 {
    constructor(element, environment) {
        this.element = element;
        this.environment = environment;
        this.allMarkers = [];
        this.categorizedMarkers = {};
        this.editable = this.element.classList.contains("editMarker");
        this.bounds = new L.LatLngBounds([
            [environment.extConf.defaultLatitude, environment.extConf.defaultLongitude]
        ]);
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
        }
        else {
            this.createMarkerBasedOnPOICollections();
        }
    }
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
        }
        else {
            this.map.panTo([this.poiCollections[0].latitude, this.poiCollections[0].longitude]);
        }
    }
    shouldFitBounds() {
        return this.getSettings().forceZoom === false
            && (this.poiCollections.length > 1
                || (this.poiCollections.length === 1
                    && (this.poiCollections[0].collectionType === "Area"
                        || this.poiCollections[0].collectionType === "Route")));
    }
    setMapDimensions() {
        this.element.style.height = this.normalizeDimension(this.getSettings().mapHeight);
        this.element.style.width = this.normalizeDimension(this.getSettings().mapWidth);
    }
    normalizeDimension(dimension) {
        let normalizedDimension = String(dimension);
        if (this.canBeInterpretedAsNumber(normalizedDimension)) {
            normalizedDimension += 'px';
        }
        return normalizedDimension;
    }
    createMap() {
        this.map = L.map(this.element, {
            center: [this.getExtConf().defaultLatitude, this.getExtConf().defaultLongitude],
            zoom: this.getSettings().zoom ? this.getSettings().zoom : 12,
            editable: this.editable,
            scrollWheelZoom: this.getSettings().activateScrollWheel !== "0"
        });
        L.tileLayer(this.getSettings().mapTile, {
            attribution: this.getSettings().mapTileAttribution,
            maxZoom: 20
        }).addTo(this.map);
    }
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
    getCategoriesOfCheckboxesWithStatus(form, isChecked) {
        let categories = [];
        let checkboxes = isChecked
            ? Array.from(form.querySelectorAll("input:checked"))
            : Array.from(form.querySelectorAll("input:not(:checked)"));
        checkboxes.forEach((checkbox) => {
            categories.push(parseInt(checkbox.value));
        });
        return categories;
    }
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
        var _a;
        let categories = this.groupCategories();
        let form = document.createElement("form");
        form.classList.add("txMaps2Form");
        form.setAttribute("id", "txMaps2Form-" + this.getContentRecord().uid);
        // Add checkbox for category
        for (let categoryUid in categories) {
            if (categories.hasOwnProperty(categoryUid)) {
                form.appendChild(this.getCheckbox(categories[categoryUid]));
                (_a = form.querySelector("#checkCategory_" + categoryUid)) === null || _a === void 0 ? void 0 : _a.insertAdjacentHTML("afterend", `<span class="map-category">${categories[categoryUid].title}</span>`);
            }
        }
        // Add listener for checkboxes
        form.querySelectorAll("input").forEach((checkbox) => {
            checkbox.addEventListener("click", () => {
                let isChecked = checkbox.checked;
                let categoryUid = checkbox.value;
                let markers = this.getMarkersToChangeVisibilityFor(categoryUid, form, isChecked);
                markers.forEach((marker) => {
                    if (isChecked) {
                        this.map.addLayer(marker);
                    }
                    else {
                        this.map.removeLayer(marker);
                    }
                });
            });
        });
        this.element.insertAdjacentElement("afterend", form);
    }
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
        for (let i = 0; i < this.poiCollections.length; i++) {
            if (this.poiCollections[i].strokeColor === "") {
                this.poiCollections[i].strokeColor = this.getExtConf().strokeColor;
            }
            if (this.poiCollections[i].strokeOpacity === 0) {
                this.poiCollections[i].strokeOpacity = this.getExtConf().strokeOpacity;
            }
            // ... (similar updates for other properties)
            marker = null;
            switch (this.poiCollections[i].collectionType) {
                case "Point":
                    marker = this.createMarker(this.poiCollections[i]);
                    break;
                case "Area":
                    marker = this.createArea(this.poiCollections[i]);
                    break;
                case "Route":
                    marker = this.createRoute(this.poiCollections[i]);
                    break;
                case "Radius":
                    marker = this.createRadius(this.poiCollections[i]);
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
    createMarkerByLatLng(latitude, longitude) {
        let marker = L.marker([latitude, longitude]).addTo(this.map);
        this.bounds.extend(marker.getLatLng());
    }
    createMarker(poiCollection) {
        let marker = L.marker([poiCollection.latitude, poiCollection.longitude], {
            'draggable': this.editable
        }).addTo(this.map);
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
        }
        else {
            this.addInfoWindow(marker, poiCollection);
        }
        return marker;
    }
    createArea(poiCollection) {
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
        this.addInfoWindow(marker, poiCollection);
        return marker;
    }
    createRoute(poiCollection) {
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
        this.addInfoWindow(marker, poiCollection);
        return marker;
    }
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
    addEditListeners(mapContainer, marker, poiCollection) {
        marker.on('dragend', () => {
            var _a, _b;
            let lat = marker.getLatLng().lat.toFixed(6);
            let lng = marker.getLatLng().lng.toFixed(6);
            (_a = mapContainer
                .previousElementSibling) === null || _a === void 0 ? void 0 : _a.querySelector(`input.latitude-${this.getContentRecord().uid}`).setAttribute("value", lat);
            (_b = mapContainer
                .previousElementSibling) === null || _b === void 0 ? void 0 : _b.querySelector(`input.longitude-${this.getContentRecord().uid}`).setAttribute("value", lng);
        });
        this.map.on('click', (event) => {
            var _a, _b;
            marker.setLatLng(event.latlng);
            (_a = mapContainer
                .previousElementSibling) === null || _a === void 0 ? void 0 : _a.querySelector(`input.latitude-${this.getContentRecord().uid}`).setAttribute("value", event.latlng.lat.toFixed(6));
            (_b = mapContainer
                .previousElementSibling) === null || _b === void 0 ? void 0 : _b.querySelector(`input.longitude-${this.getContentRecord().uid}`).setAttribute("value", event.latlng.lng.toFixed(6));
        });
    }
    canBeInterpretedAsNumber(value) {
        return typeof value === 'number' || !isNaN(Number(value));
    }
    getContentRecord() {
        return this.environment.contentRecord;
    }
    getExtConf() {
        return this.environment.extConf;
    }
    getSettings() {
        return this.environment.settings;
    }
}
let maps2OpenStreetMaps = [];
document.querySelectorAll(".maps2").forEach((element) => {
    const environment = typeof element.dataset.environment !== 'undefined' ? element.dataset.environment : '{}';
    const override = typeof element.dataset.override !== 'undefined' ? element.dataset.override : '{}';
    maps2OpenStreetMaps.push(new OpenStreetMap2(element, Object.assign(Object.assign({}, JSON.parse(environment)), JSON.parse(override))));
});
