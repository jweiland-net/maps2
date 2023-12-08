var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var OpenStreetMap2 = /** @class */ (function () {
    function OpenStreetMap2(element, environment) {
        this.element = element;
        this.environment = environment;
        this.allMarkers = [];
        this.categorizedMarkers = {};
        this.bounds = new L.LatLngBounds([
            [environment.extConf.defaultLatitude, environment.extConf.defaultLongitude]
        ]);
        this.poiCollections = JSON.parse(this.element.getAttribute("data-pois") || "[]");
        this.editable = this.element.classList.contains("editMarker");
        this.setWidthAndHeight();
        this.createMap();
        if (typeof this.poiCollections === "undefined" || this.poiCollections.length === 0) {
            // Plugin: CityMap
            var lat = parseFloat(this.element.getAttribute("data-latitude") || "");
            var lng = parseFloat(this.element.getAttribute("data-longitude") || "");
            if (!isNaN(lat) && !isNaN(lng)) {
                this.createMarkerByLatLng(lat, lng);
            }
        }
        else {
            this.createPointByCollectionType(environment);
            if (this.countObjectProperties(this.categorizedMarkers) > 1) {
                this.showSwitchableCategories(environment);
            }
            if (environment.settings.forceZoom === false
                && (this.poiCollections.length > 1
                    || (this.poiCollections.length === 1
                        && (this.poiCollections[0].collectionType === "Area"
                            || this.poiCollections[0].collectionType === "Route")))) {
                this.map.fitBounds(this.bounds);
            }
            else {
                this.map.panTo([this.poiCollections[0].latitude, this.poiCollections[0].longitude]);
            }
        }
    }
    OpenStreetMap2.prototype.setWidthAndHeight = function () {
        var height = String(this.environment.settings.mapHeight);
        if (this.canBeInterpretedAsNumber(height)) {
            height += "px";
        }
        var width = String(this.environment.settings.mapWidth);
        if (this.canBeInterpretedAsNumber(width)) {
            width += "px";
        }
        this.element.style.height = height;
        this.element.style.width = width;
    };
    OpenStreetMap2.prototype.createMap = function () {
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
    };
    OpenStreetMap2.prototype.groupCategories = function (environment) {
        var me = this;
        var groupedCategories = {};
        var categoryUid = "0";
        for (var x = 0; x < this.poiCollections.length; x++) {
            for (var y = 0; y < this.poiCollections[x].categories.length; y++) {
                categoryUid = String(this.poiCollections[x].categories[y].uid);
                if (this.inList(environment.settings.categories, categoryUid) > -1 && !groupedCategories.hasOwnProperty(categoryUid)) {
                    groupedCategories[categoryUid] = this.poiCollections[x].categories[y];
                }
            }
        }
        return groupedCategories;
    };
    OpenStreetMap2.prototype.getCategoriesOfCheckboxesWithStatus = function (form, isChecked) {
        var categories = [];
        var checkboxes = isChecked ? form.querySelectorAll("input:checked") : form.querySelectorAll("input:not(:checked)");
        checkboxes.forEach(function (checkbox) {
            categories.push(parseInt(checkbox.value));
        });
        return categories;
    };
    OpenStreetMap2.prototype.getMarkersToChangeVisibilityFor = function (categoryUid, form, isChecked) {
        var markers = [];
        if (this.allMarkers.length === 0) {
            return markers;
        }
        var marker = null;
        var allCategoriesOfMarker = null;
        var categoriesOfCheckboxesWithStatus = this.getCategoriesOfCheckboxesWithStatus(form, isChecked);
        for (var i = 0; i < this.allMarkers.length; i++) {
            marker = this.allMarkers[i];
            allCategoriesOfMarker = marker.poiCollection.categories;
            if (allCategoriesOfMarker.length === 0) {
                continue;
            }
            var markerCategoryHasCheckboxWithStatus = void 0;
            for (var j = 0; j < allCategoriesOfMarker.length; j++) {
                markerCategoryHasCheckboxWithStatus = false;
                for (var k = 0; k < categoriesOfCheckboxesWithStatus.length; k++) {
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
    };
    OpenStreetMap2.prototype.showSwitchableCategories = function (environment) {
        var _this = this;
        var _a;
        var categories = this.groupCategories(environment);
        var form = document.createElement("form");
        form.classList.add("txMaps2Form");
        form.setAttribute("id", "txMaps2Form-" + environment.contentRecord.uid);
        // Add checkbox for category
        for (var categoryUid in categories) {
            if (categories.hasOwnProperty(categoryUid)) {
                form.appendChild(this.getCheckbox(categories[categoryUid]));
                (_a = form.querySelector("#checkCategory_" + categoryUid)) === null || _a === void 0 ? void 0 : _a.insertAdjacentHTML("afterend", "<span class=\"map-category\">".concat(categories[categoryUid].title, "</span>"));
            }
        }
        // Add listener for checkboxes
        form.querySelectorAll("input").forEach(function (checkbox) {
            checkbox.addEventListener("click", function () {
                var isChecked = checkbox.checked;
                var categoryUid = checkbox.value;
                var markers = _this.getMarkersToChangeVisibilityFor(categoryUid, form, isChecked);
                markers.forEach(function (marker) {
                    if (isChecked) {
                        _this.map.addLayer(marker);
                    }
                    else {
                        _this.map.removeLayer(marker);
                    }
                });
            });
        });
        this.element.insertAdjacentElement("afterend", form);
    };
    OpenStreetMap2.prototype.getCheckbox = function (category) {
        var div = document.createElement("div");
        div.classList.add("form-group");
        div.innerHTML = "\n            <div class=\"checkbox\">\n                <label>\n                    <input type=\"checkbox\" class=\"checkCategory\" id=\"checkCategory_".concat(category.uid, "\" checked=\"checked\" value=\"").concat(category.uid, "\">\n                </label>\n            </div>");
        return div;
    };
    OpenStreetMap2.prototype.countObjectProperties = function (obj) {
        var count = 0;
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                count++;
            }
        }
        return count;
    };
    OpenStreetMap2.prototype.createPointByCollectionType = function (environment) {
        var marker;
        var categoryUid = 0;
        for (var i = 0; i < this.poiCollections.length; i++) {
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
            for (var c = 0; c < this.poiCollections[i].categories.length; c++) {
                categoryUid = this.poiCollections[i].categories[c].uid;
                if (!this.categorizedMarkers.hasOwnProperty(categoryUid)) {
                    this.categorizedMarkers[categoryUid] = [];
                }
                this.categorizedMarkers[categoryUid].push(marker);
            }
        }
    };
    OpenStreetMap2.prototype.createMarkerByLatLng = function (latitude, longitude) {
        var marker = L.marker([latitude, longitude]).addTo(this.map);
        this.bounds.extend(marker.getLatLng());
    };
    OpenStreetMap2.prototype.inList = function (list, item) {
        var catSearch = ',' + list + ',';
        item = ',' + item + ',';
        return catSearch.indexOf(item);
    };
    OpenStreetMap2.prototype.createMarker = function (poiCollection, environment) {
        var marker = L.marker([poiCollection.latitude, poiCollection.longitude], {
            'draggable': this.editable
        }).addTo(this.map);
        if (poiCollection.hasOwnProperty("markerIcon") && poiCollection.markerIcon !== "") {
            var icon = L.icon({
                iconUrl: poiCollection.markerIcon,
                iconSize: [poiCollection.markerIconWidth, poiCollection.markerIconHeight],
                iconAnchor: [poiCollection.markerIconAnchorPosX, poiCollection.markerIconAnchorPosY]
            });
            marker.setIcon(icon);
        }
        this.bounds.extend(marker.getLatLng());
        if (this.editable) {
            this.addEditListeners(this.element, marker, poiCollection, environment);
        }
        else {
            this.addInfoWindow(marker, poiCollection, environment);
        }
        return marker;
    };
    OpenStreetMap2.prototype.createArea = function (poiCollection, environment) {
        var latlngs = [];
        for (var i = 0; i < poiCollection.pois.length; i++) {
            var latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
            this.bounds.extend(latLng);
            latlngs.push(latLng);
        }
        var marker = L.polygon(latlngs, {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            weight: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity
        }).addTo(this.map);
        this.addInfoWindow(marker, poiCollection, environment);
        return marker;
    };
    OpenStreetMap2.prototype.createRoute = function (poiCollection, environment) {
        var latlngs = [];
        for (var i = 0; i < poiCollection.pois.length; i++) {
            var latLng = [poiCollection.pois[i].latitude, poiCollection.pois[i].longitude];
            this.bounds.extend(latLng);
            latlngs.push(latLng);
        }
        var marker = L.polyline(latlngs, {
            color: poiCollection.strokeColor,
            opacity: poiCollection.strokeOpacity,
            weight: poiCollection.strokeWeight,
            fillColor: poiCollection.fillColor,
            fillOpacity: poiCollection.fillOpacity
        }).addTo(this.map);
        this.addInfoWindow(marker, poiCollection, environment);
        return marker;
    };
    OpenStreetMap2.prototype.createRadius = function (poiCollection, environment) {
        var marker = L.circle([poiCollection.latitude, poiCollection.longitude], {
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
    };
    OpenStreetMap2.prototype.addInfoWindow = function (element, poiCollection, environment) {
        element.addEventListener("click", function () {
            fetch(environment.ajaxUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    poiCollection: poiCollection.uid
                })
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                element.bindPopup(data.content).openPopup();
            })
                .catch(function (error) { return console.error('Error:', error); });
        });
    };
    OpenStreetMap2.prototype.addEditListeners = function (mapContainer, marker, poiCollection, environment) {
        marker.on('dragend', function () {
            var _a, _b;
            var lat = marker.getLatLng().lat.toFixed(6);
            var lng = marker.getLatLng().lng.toFixed(6);
            (_a = mapContainer
                .previousElementSibling) === null || _a === void 0 ? void 0 : _a.querySelector("input.latitude-".concat(environment.contentRecord.uid)).setAttribute("value", lat);
            (_b = mapContainer
                .previousElementSibling) === null || _b === void 0 ? void 0 : _b.querySelector("input.longitude-".concat(environment.contentRecord.uid)).setAttribute("value", lng);
        });
        this.map.on('click', function (event) {
            var _a, _b;
            marker.setLatLng(event.latlng);
            (_a = mapContainer
                .previousElementSibling) === null || _a === void 0 ? void 0 : _a.querySelector("input.latitude-".concat(environment.contentRecord.uid)).setAttribute("value", event.latlng.lat.toFixed(6));
            (_b = mapContainer
                .previousElementSibling) === null || _b === void 0 ? void 0 : _b.querySelector("input.longitude-".concat(environment.contentRecord.uid)).setAttribute("value", event.latlng.lng.toFixed(6));
        });
    };
    OpenStreetMap2.prototype.canBeInterpretedAsNumber = function (value) {
        return typeof value === 'number' || !isNaN(Number(value));
    };
    OpenStreetMap2.prototype.getExtConf = function () {
        return this.environment.extConf;
    };
    OpenStreetMap2.prototype.getSettings = function () {
        return this.environment.settings;
    };
    return OpenStreetMap2;
}());
var maps2OpenStreetMaps = [];
document.querySelectorAll(".maps2").forEach(function (element) {
    var environment = typeof element.dataset.environment !== 'undefined' ? element.dataset.environment : '{}';
    var override = typeof element.dataset.override !== 'undefined' ? element.dataset.override : '{}';
    maps2OpenStreetMaps.push(new OpenStreetMap2(element, __assign(__assign({}, JSON.parse(environment)), JSON.parse(override))));
});
