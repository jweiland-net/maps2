/**
 * Create a MapOptions object which can be assigned to the Map object of Google
 *
 * @param settings
 * @constructor
 */
function MapOptions(settings) {
	this.zoom = parseInt(settings.zoom);
	this.panControl = settings.panControl;
	this.zoomControl = settings.zoomControl;
	this.mapTypeControl = settings.mapTypeControl;
	this.scaleControl = settings.scaleControl;
	this.streetViewControl = settings.streetViewControl;
	this.overviewMapControl = settings.overviewMapControl;
	this.setMapTypeId(settings.mapTypeId);
}

/**
 * Create CircleOptions which can be assigned to the Circle object of Google
 *
 * @param map
 * @param centerPosition
 * @param poiCollection
 * @constructor
 */
function CircleOptions(map, centerPosition, poiCollection) {
	this.map = map;
	this.center = centerPosition;
	this.radius = poiCollection.radius;
	this.strokeColor = poiCollection.strokeColor;
	this.strokeOpacity = poiCollection.strokeOpacity;
	this.strokeWeight = poiCollection.strokeWeight;
	this.fillColor = poiCollection.fillColor;
	this.fillOpacity = poiCollection.fillOpacity;
}

/**
 * Create PolygonOptions which can be assigned to the Polygon object of Google
 *
 * @param paths
 * @param poiCollection
 * @constructor
 */
function PolygonOptions(paths, poiCollection) {
	this.paths = paths;
	this.strokeColor = poiCollection.strokeColor;
	this.strokeOpacity = poiCollection.strokeOpacity;
	this.strokeWeight = poiCollection.strokeWeight;
	this.fillColor = poiCollection.fillColor;
	this.fillOpacity = poiCollection.fillOpacity;
}

/**
 * Create PolylineOptions which can be assigned to the Polyline object of Google
 *
 * @param paths
 * @param poiCollection
 * @constructor
 */
function PolylineOptions(paths, poiCollection) {
	this.path = paths;
	this.strokeColor = poiCollection.strokeColor;
	this.strokeOpacity = poiCollection.strokeOpacity;
	this.strokeWeight = poiCollection.strokeWeight;
}

/**
 * Instead of using eval() I decided to create a switch construct
 * which is more save
 *
 * @param mapTypeId
 */
MapOptions.prototype.setMapTypeId = function(mapTypeId) {
	switch (mapTypeId) {
		case "google.maps.MapTypeId.HYBRID":
			this.mapTypeId = google.maps.MapTypeId.HYBRID;
			break;
		case "google.maps.MapTypeId.ROADMAP":
			this.mapTypeId = google.maps.MapTypeId.ROADMAP;
			break;
		case "google.maps.MapTypeId.SATELLITE":
			this.mapTypeId = google.maps.MapTypeId.SATELLITE;
			break;
		case "google.maps.MapTypeId.TERRAIN":
			this.mapTypeId = google.maps.MapTypeId.TERRAIN;
			break;
	}
};

/**
 * Initialize a Google Map
 *
 * @param poiCollections
 * @param environment contains settings, current PageId, extConf and current tt_content record
 * @param editable
 * @constructor
 */
function Maps2(poiCollections, environment, editable) {
	this.markers = {};
	this.bounds = new google.maps.LatLngBounds();
	this.infoWindow = new google.maps.InfoWindow();
	this.$element = jQuery("#maps2-" + environment.contentRecord.uid).css({
		height: environment.settings.mapHeight,
		width: environment.settings.mapWidth
	});

	this.createMap(environment);

	if (typeof poiCollections == "undefined") {
		// Plugin: CityMap
		var lat = this.$element.data("latitude");
		var lng = this.$element.data("longitude");
		if (lat && lng) {
			this.createMarkerByLatLng(lat, lng);
			this.map.setCenter(new google.maps.LatLng(lat, lng));
			this.map.setZoom(15);
		} else {
			// Fallback
			this.map.setCenter(new google.maps.LatLng(environment.extConf.defaultLatitude, environment.extConf.defaultLongitude));
		}
	} else {
		// normal case
		this.createPointByCollectionType(poiCollections, environment, editable);
		if (this.countObjectProperties(this.markers) > 1) {
			this.showSwitchableCategories(poiCollections, environment.contentRecord);
		}
		if (poiCollections.length > 1) {
			this.map.fitBounds(this.bounds);
		} else {
			this.map.setCenter(new google.maps.LatLng(poiCollections[0].latitude, poiCollections[0].longitude));
		}
	}
}

/**
 * Create Map
 *
 * @param environment
 */
Maps2.prototype.createMap = function(environment) {
	this.map = new google.maps.Map(
		this.$element.get(0),
		new MapOptions(environment.settings)
	);
};

/**
 * Group Categories
 *
 * @param poiCollections
 */
Maps2.prototype.groupCategories = function(poiCollections) {
	var groupedCategories = {};
	var categoryUid = "0";
	for (var x = 0; x < poiCollections.length; x++) {
		for (var y = 0; y < poiCollections[x].categories.length; y++) {
			categoryUid = String(poiCollections[x].categories[y].uid);
			if (!groupedCategories.hasOwnProperty(categoryUid)) {
				groupedCategories[categoryUid] = poiCollections[x].categories[y];
			}
		}
	}
	return groupedCategories;
};

/**
 * Show switchable categories
 *
 * @param poiCollections
 * @param contentRecord
 */
Maps2.prototype.showSwitchableCategories = function(poiCollections, contentRecord) {
	var categories = this.groupCategories(poiCollections);
	var $form = jQuery("<form>")
		.addClass("txMaps2Form")
		.attr("id", "txMaps2Form-" + contentRecord.uid);

	// Add checkbox for category
	for (var categoryUid in categories) {
		if (categories.hasOwnProperty(categoryUid)) {
			$form.append(this.getCheckbox(categories[categoryUid]));
		}
	}
	// create form
	var markers = this.markers;
	$form.find("input").on("click", function() {
		var isChecked = jQuery(this).is(":checked");
		var categoryUid = jQuery(this).val();
		if (markers.hasOwnProperty(categoryUid)) {
			for (var i = 0; i < markers[categoryUid].length; i++) {
				markers[categoryUid][i].setVisible(isChecked);
			}
		}
	});
	this.$element.after($form);

};

/**
 * Get Checkbox for Category
 *
 * @param category
 */
Maps2.prototype.getCheckbox = function(category) {
	return jQuery("<div />")
		.addClass("form-group").append(
			jQuery("<div />")
				.addClass("checkbox").append(
				jQuery("<label />").append(
					jQuery("<input />")
						.attr({
							type: "checkbox",
							checked: "checked",
							value: category.uid
						})
						.after(
							jQuery("<span />")
								.addClass("map-category")
								.text(category.title)
						)
				)
			)
		);
};

/**
 * Count Object properties
 *
 * @param obj
 */
Maps2.prototype.countObjectProperties = function(obj) {
	var count = 0;
	for (var key in obj) {
		if (obj.hasOwnProperty(key)) {
			count++;
		}
	}
	return count;
};

/**
 * Create Point by CollectionType
 *
 * @param poiCollections
 * @param environment
 * @param editable
 */
Maps2.prototype.createPointByCollectionType = function(poiCollections, environment, editable) {
	for (var i = 0; i < poiCollections.length; i++) {
		if (poiCollections[i].strokeColor == "") {
			poiCollections[i].strokeColor = environment.extConf.strokeColor;
		}
		if (poiCollections[i].strokeOpacity == "") {
			poiCollections[i].strokeOpacity = environment.extConf.strokeOpacity;
		}
		if (poiCollections[i].strokeWeight == "") {
			poiCollections[i].strokeWeight = environment.extConf.strokeWeight;
		}
		if (poiCollections[i].fillColor == "") {
			poiCollections[i].fillColor = environment.extConf.fillColor;
		}
		if (poiCollections[i].fillOpacity == "") {
			poiCollections[i].fillOpacity = environment.extConf.fillOpacity;
		}
		switch (poiCollections[i].collectionType) {
			case "Point":
				this.createMarker(poiCollections[i], environment, editable);
				break;
			case "Area":
				this.createArea(poiCollections[i], environment.extConf);
				break;
			case "Route":
				this.createRoute(poiCollections[i], environment.extConf);
				break;
			case "Radius":
				this.createRadius(poiCollections[i], environment.extConf);
				break;
		}
	}
};

/**
 * Create Marker with InfoWindow
 *
 * @param poiCollection
 * @param environment
 * @param editable
 */
Maps2.prototype.createMarker = function(poiCollection, environment, editable) {
	var categoryUid = "0";
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
		map: this.map
	});
	marker.setDraggable(editable);
	for (var i = 0; i < poiCollection.categories.length; i++) {
		categoryUid = poiCollection.categories[i].uid;
		if (!this.markers.hasOwnProperty(categoryUid)) {
			this.markers[categoryUid] = [];
		}
		// assign first category icon to marker
		if (i === 0 && poiCollection.categories[i].markerIcon != "") {
			var icon = {
				url: poiCollection.categories[i].markerIcon,
				scaledSize: new google.maps.Size(25, 40),
				anchor: new google.maps.Point(13, 40)
			};
			marker.setIcon(icon);
		}
		this.markers[categoryUid].push(marker);
	}
	this.bounds.extend(marker.position);

	// we need these both vars to be set global. So that we can access them in Listener
	var infoWindow = this.infoWindow;
	var map = this.map;

	if (editable) {
		this.addEditListeners(marker, poiCollection, environment);
	} else {
		google.maps.event.addListener(marker, "click", function() {
			infoWindow.close();
			infoWindow.setContent(poiCollection.infoWindowContent);
			infoWindow.open(map, marker);
		});
	}
};

/**
 * Create Marker with InfoWindow
 *
 * @param latitude
 * @param longitude
 */
Maps2.prototype.createMarkerByLatLng = function(latitude, longitude) {
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(latitude, longitude),
		map: this.map
	});
	this.bounds.extend(marker.position);
};

/**
 * Create Area
 *
 * @param poiCollection
 */
Maps2.prototype.createArea = function(poiCollection) {
	var latLng;
	var paths = [];
	for (var i = 0; i < poiCollection.pois.length; i++) {
		latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
		this.bounds.extend(latLng);
		paths.push(latLng);
	}

	if (paths.length == 0) {
		paths.push(this.mapPosition);
	}

	var area = new google.maps.Polygon(new PolygonOptions(paths, poiCollection));
	area.setMap(this.map);
};

/**
 * Create Route
 *
 * @param poiCollection
 */
Maps2.prototype.createRoute = function(poiCollection) {
	var latLng;
	var paths = [];
	for (var i = 0; i < poiCollection.pois.length; i++) {
		latLng = new google.maps.LatLng(poiCollection.pois[i].latitude, poiCollection.pois[i].longitude);
		this.bounds.extend(latLng);
		paths.push(latLng);
	}

	if (paths.length == 0) {
		paths.push(this.mapPosition);
	}

	var route = new google.maps.Polyline(new PolylineOptions(paths, poiCollection));
	route.setMap(this.map);
};

/**
 * Create Radius
 *
 * @param poiCollection
 */
Maps2.prototype.createRadius = function(poiCollection) {
	var circle = new google.maps.Circle(
		new CircleOptions(
			this.map,
			new google.maps.LatLng(poiCollection.latitude, poiCollection.longitude),
			poiCollection
		)
	);
	this.bounds.union(circle.getBounds());
};

/**
 * Add Edit Listeners
 * This will only work for Markers (Point)
 *
 * @param marker
 * @param poiCollection
 * @param environment
 */
Maps2.prototype.addEditListeners = function(marker, poiCollection, environment) {
	// update fields and marker while dragging
	google.maps.event.addListener(marker, 'dragend', function() {
		var lat = marker.getPosition().lat().toFixed(6);
		var lng = marker.getPosition().lng().toFixed(6);
		jQuery("input#latitude-" + environment.contentRecord.uid).val(lat);
		jQuery("input#longitude-" + environment.contentRecord.uid).val(lng);
	});

	// update fields and marker when clicking on the map
	google.maps.event.addListener(this.map, 'click', function(event) {
		marker.setPosition(event.latLng);
		jQuery("input#latitude-" + environment.contentRecord.uid).val(event.latLng.lat().toFixed(6));
		jQuery("input#longitude-" + environment.contentRecord.uid).val(event.latLng.lng().toFixed(6));
	});
};

/**
 * This function will be called by the &callback argument of the Google Maps API library
 */
function initMap() {
	var $element;
	var environment;
	jQuery(".maps2").each(function() {
		$element = jQuery(this);
		// override environment with settings of override
		environment = jQuery.extend(true, $element.data("environment"), $element.data("override"));
		new Maps2($element.data("pois"), environment, $element.hasClass("editMarker"));
	});
}