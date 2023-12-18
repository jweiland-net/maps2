export class Category {
  #uid = '';
  #title = '';

  /**
   * @constructor
   * @param {string} uid
   * @param {string} title
   */
  constructor(uid, title) {
    this.#uid = uid;
    this.#title = title;
  };

  /**
   * @returns {string}
   */
  get uid() {
    return this.#uid;
  }

  /**
   * @returns {string}
   */
  get title() {
    return this.#title;
  }
}

export class ContentRecord {
  #CType = '';
  #bodytext = '';
  #colPos = '';
  #crdate = '';
  #date = '';
  #endtime = '';
  #frame_class = '';
  #header = '';
  #hidden = '';
  #imageheight = '';
  #imagewidth = '';
  #list_type = '';
  #pages = '';
  #pid = '';
  #starttime = '';
  #subheader = '';
  #sys_language_uid = '';
  #target = '';
  #tstamp = '';
  #uid = '';

  /**
   * @constructor
   * @param {object} contentRecord
   */
  constructor(contentRecord) {
    this.#CType = contentRecord.CType;
    this.#bodytext = contentRecord.bodytext;
    this.#colPos = contentRecord.colPos;
    this.#crdate = contentRecord.crdate;
    this.#date = contentRecord.date;
    this.#endtime = contentRecord.endtime;
    this.#frame_class = contentRecord.frame_class;
    this.#header = contentRecord.header;
    this.#hidden = contentRecord.hidden;
    this.#imageheight = contentRecord.imageheight;
    this.#imagewidth = contentRecord.imagewidth;
    this.#list_type = contentRecord.list_type;
    this.#pages = contentRecord.pages;
    this.#pid = contentRecord.pid;
    this.#starttime = contentRecord.starttime;
    this.#subheader = contentRecord.subheader;
    this.#sys_language_uid = contentRecord.sys_language_uid;
    this.#target = contentRecord.target;
    this.#tstamp = contentRecord.tstamp;
    this.#uid = contentRecord.uid;
  };

  /**
   * @returns {string}
   */
  get CType() {
    return this.#CType;
  }

  /**
   * @returns {string}
   */
  get bodytext() {
    return this.#bodytext;
  }

  /**
   * @returns {string}
   */
  get colPos() {
    return this.#colPos;
  }

  /**
   * @returns {string}
   */
  get crdate() {
    return this.#crdate;
  }

  /**
   * @returns {string}
   */
  get date() {
    return this.#date;
  }

  /**
   * @returns {string}
   */
  get endtime() {
    return this.#endtime;
  }

  /**
   * @returns {string}
   */
  get frame_class() {
    return this.#frame_class;
  }

  /**
   * @returns {string}
   */
  get header() {
    return this.#header;
  }

  /**
   * @returns {string}
   */
  get hidden() {
    return this.#hidden;
  }

  /**
   * @returns {string}
   */
  get imageheight() {
    return this.#imageheight;
  }

  /**
   * @returns {string}
   */
  get imagewidth() {
    return this.#imagewidth;
  }

  /**
   * @returns {string}
   */
  get list_type() {
    return this.#list_type;
  }

  /**
   * @returns {string}
   */
  get pages() {
    return this.#pages;
  }

  /**
   * @returns {string}
   */
  get pid() {
    return this.#pid;
  }

  /**
   * @returns {string}
   */
  get starttime() {
    return this.#starttime;
  }

  /**
   * @returns {string}
   */
  get subheader() {
    return this.#subheader;
  }

  /**
   * @returns {string}
   */
  get sys_language_uid() {
    return this.#sys_language_uid;
  }

  /**
   * @returns {string}
   */
  get target() {
    return this.#target;
  }

  /**
   * @returns {string}
   */
  get tstamp() {
    return this.#tstamp;
  }

  /**
   * @returns {string}
   */
  get uid() {
    return this.#uid;
  }
}

export class Environment {
  #ajaxUrl = '';
  #contentRecord = {};
  #extConf = {};
  #settings = {};
  #lat = '';
  #lng = '';

  /**
   * @constructor
   * @param {object} environment
   */
  constructor(environment) {
    this.#ajaxUrl = environment.ajaxUrl;
    this.#contentRecord = environment.contentRecord;
    this.#extConf = environment.extConf;
    this.#settings = environment.settings;
    this.#lat = environment.lat;
    this.#lng = environment.lng;
  }

  /**
   * @returns {string}
   */
  get ajaxUrl() {
    return this.#ajaxUrl;
  }

  /**
   * @returns {string}
   */
  get contentRecord() {
    return this.#contentRecord;
  }

  /**
   * @returns {string}
   */
  get extConf() {
    return this.#extConf;
  }

  /**
   * @returns {string}
   */
  get settings() {
    return this.#settings;
  }

  /**
   * @returns {string}
   */
  get lat() {
    return this.#lat;
  }

  /**
   * @returns {string}
   */
  get lng() {
    return this.#lng;
  }
}

export class ExtConf {
  #defaultCountry = '';
  #defaultLatitude = '';
  #defaultLongitude = '';
  #defaultMapProvider = '';
  #defaultMapType = '';
  #defaultRadius = '';
  #explicitAllowMapProviderRequests = '';
  #explicitAllowMapProviderRequestsBySessionOnly = '';
  #fillColor = '';
  #fillOpacity = '';
  #googleMapsGeocodeApiKey = '';
  #googleMapsGeocodeUri = '';
  #googleMapsJavaScriptApiKey = '';
  #googleMapsLibrary = '';
  #infoWindowContentTemplatePath = '';
  #mapProvider = '';
  #markerIconAnchorPosX = '';
  #markerIconAnchorPosY = '';
  #markerIconHeight = '';
  #markerIconWidth = '';
  #openStreetMapGeocodeUri = '';
  #strokeColor = '';
  #strokeOpacity = '';
  #strokeWeight = '';

  /**
   * @constructor
   * @param {object} extConf
   */
  constructor(extConf) {
    this.#defaultCountry = extConf.defaultCountry;
    this.#defaultLatitude = extConf.defaultLatitude;
    this.#defaultLongitude = extConf.defaultLongitude;
    this.#defaultMapProvider = extConf.defaultMapProvider;
    this.#defaultMapType = extConf.defaultMapType;
    this.#defaultRadius = extConf.defaultRadius;
    this.#explicitAllowMapProviderRequests = extConf.explicitAllowMapProviderRequests;
    this.#explicitAllowMapProviderRequestsBySessionOnly = extConf.explicitAllowMapProviderRequestsBySessionOnly;
    this.#fillColor = extConf.fillColor;
    this.#fillOpacity = extConf.fillOpacity;
    this.#googleMapsGeocodeApiKey = extConf.googleMapsGeocodeApiKey;
    this.#googleMapsGeocodeUri = extConf.googleMapsGeocodeUri;
    this.#googleMapsJavaScriptApiKey = extConf.googleMapsJavaScriptApiKey;
    this.#googleMapsLibrary = extConf.googleMapsLibrary;
    this.#infoWindowContentTemplatePath = extConf.infoWindowContentTemplatePath;
    this.#mapProvider = extConf.mapProvider;
    this.#markerIconAnchorPosX = extConf.markerIconAnchorPosX;
    this.#markerIconAnchorPosY = extConf.markerIconAnchorPosY;
    this.#markerIconHeight = extConf.markerIconHeight;
    this.#markerIconWidth = extConf.markerIconWidth;
    this.#openStreetMapGeocodeUri = extConf.openStreetMapGeocodeUri;
    this.#strokeColor = extConf.strokeColor;
    this.#strokeOpacity = extConf.strokeOpacity;
    this.#strokeWeight = extConf.strokeWeight;
  };

  /**
   * @returns {string}
   */
  get defaultCountry() {
    return this.#defaultCountry;
  }

  /**
   * @returns {string}
   */
  get defaultLatitude() {
    return this.#defaultLatitude;
  }

  /**
   * @returns {string}
   */
  get defaultLongitude() {
    return this.#defaultLongitude;
  }

  /**
   * @returns {string}
   */
  get defaultMapProvider() {
    return this.#defaultMapProvider;
  }

  /**
   * @returns {string}
   */
  get defaultMapType() {
    return this.#defaultMapType;
  }

  /**
   * @returns {string}
   */
  get defaultRadius() {
    return this.#defaultRadius;
  }

  /**
   * @returns {string}
   */
  get explicitAllowMapProviderRequests() {
    return this.#explicitAllowMapProviderRequests;
  }

  /**
   * @returns {string}
   */
  get explicitAllowMapProviderRequestsBySessionOnly() {
    return this.#explicitAllowMapProviderRequestsBySessionOnly;
  }

  /**
   * @returns {string}
   */
  get fillColor() {
    return this.#fillColor;
  }

  /**
   * @returns {string}
   */
  get fillOpacity() {
    return this.#fillOpacity;
  }

  /**
   * @returns {string}
   */
  get googleMapsGeocodeApiKey() {
    return this.#googleMapsGeocodeApiKey;
  }

  /**
   * @returns {string}
   */
  get googleMapsGeocodeUri() {
    return this.#googleMapsGeocodeUri;
  }

  /**
   * @returns {string}
   */
  get googleMapsJavaScriptApiKey() {
    return this.#googleMapsJavaScriptApiKey;
  }

  /**
   * @returns {string}
   */
  get googleMapsLibrary() {
    return this.#googleMapsLibrary;
  }

  /**
   * @returns {string}
   */
  get infoWindowContentTemplatePath() {
    return this.#infoWindowContentTemplatePath;
  }

  /**
   * @returns {string}
   */
  get mapProvider() {
    return this.#mapProvider;
  }

  /**
   * @returns {string}
   */
  get markerIconAnchorPosX() {
    return this.#markerIconAnchorPosX;
  }

  /**
   * @returns {string}
   */
  get markerIconAnchorPosY() {
    return this.#markerIconAnchorPosY;
  }

  /**
   * @returns {string}
   */
  get markerIconHeight() {
    return this.#markerIconHeight;
  }

  /**
   * @returns {string}
   */
  get markerIconWidth() {
    return this.#markerIconWidth;
  }

  /**
   * @returns {string}
   */
  get openStreetMapGeocodeUri() {
    return this.#openStreetMapGeocodeUri;
  }

  /**
   * @returns {string}
   */
  get strokeColor() {
    return this.#strokeColor;
  }

  /**
   * @returns {string}
   */
  get strokeOpacity() {
    return this.#strokeOpacity;
  }

  /**
   * @returns {string}
   */
  get strokeWeight() {
    return this.#strokeWeight;
  }
}

export class Image {
  #width = '';
  #height = '';

  /**
   * @constructor
   * @param {string} width
   * @param {string} height
   */
  constructor(width, height) {
    this.#width = width;
    this.#height = height;
  };

  /**
   * @returns {string}
   */
  get width() {
    return this.#width;
  }

  /**
   * @returns {string}
   */
  get height() {
    return this.#height;
  }
}

export class InfoWindow {
  #image = {};

  /**
   * @constructor
   * @param {Image} image
   */
  constructor(image) {
    this.#image = image;
  };

  /**
   * @returns {Image}
   */
  get image() {
    return this.#image;
  }
}

export class Link {
  #addSection = '';

  /**
   * @constructor
   * @param {boolean} addSection
   */
  constructor(addSection) {
    this.#addSection = addSection;
  };

  /**
   * @returns {boolean}
   */
  get addSection() {
    return this.#addSection;
  }
}

export class Overlay {
  #link = {};

  /**
   * @constructor
   * @param {object} link
   */
  constructor(link) {
    this.#link = link;
  };

  /**
   * @returns {Link}
   */
  get link() {
    return this.#link;
  }
}

export class PoiCollection {
  #address = '';
  #categories = '';
  #collectionType = '';
  #configurationMap = [];
  #distance = '';
  #fillColor = '';
  #fillOpacity = '';
  #foreignRecords = '';
  #infoWindowContent = '';
  #latitude = '';
  #longitude = '';
  #markerIcon = '';
  #markerIconAnchorPosX = '';
  #markerIconAnchorPosY = '';
  #markerIconHeight = '';
  #markerIconWidth = '';
  #pid = '';
  #pois = '';
  #radius = '';
  #strokeColor = '';
  #strokeOpacity = '';
  #strokeWeight = '';
  #title = '';
  #uid = '';

  /**
   * @constructor
   * @param {object} poiCollection
   */
  constructor(poiCollection) {
    this.#address = poiCollection.address;
    this.#categories = poiCollection.categories;
    this.#collectionType = typeof poiCollection.collectionType !== 'undefined' ? poiCollection.collectionType : poiCollection.collection_type;
    this.#configurationMap = typeof poiCollection.configurationMap !== 'undefined' ? poiCollection.configurationMap : poiCollection.configuration_map;
    this.#distance = poiCollection.distance;
    this.#fillColor = typeof poiCollection.fillColor !== 'undefined' ? poiCollection.fillColor : poiCollection.fill_color;
    this.#fillOpacity = typeof poiCollection.fillOpacity !== 'undefined' ? poiCollection.fillOpacity : poiCollection.fill_opacity;
    this.#foreignRecords = typeof poiCollection.foreignRecords !== 'undefined' ? poiCollection.foreignRecords : poiCollection.foreign_records;
    this.#infoWindowContent = typeof poiCollection.infoWindowContent !== 'undefined' ? poiCollection.infoWindowContent : poiCollection.info_window_content;
    this.#latitude = poiCollection.latitude;
    this.#longitude = poiCollection.longitude;
    this.#markerIcon = typeof poiCollection.markerIcon !== 'undefined' ? poiCollection.markerIcon : poiCollection.marker_icon;
    this.#markerIconAnchorPosX = typeof poiCollection.markerIconAnchorPosX !== 'undefined' ? poiCollection.markerIconAnchorPosX : poiCollection.marker_icon_pos_x;
    this.#markerIconAnchorPosY = typeof poiCollection.markerIconAnchorPosY !== 'undefined' ? poiCollection.markerIconAnchorPosY : poiCollection.marker_icon_pos_y;
    this.#markerIconHeight = typeof poiCollection.markerIconHeight !== 'undefined' ? poiCollection.markerIconHeight : poiCollection.marker_icon_height;
    this.#markerIconWidth = typeof poiCollection.markerIconWidth !== 'undefined' ? poiCollection.markerIconWidth : poiCollection.marker_icon_width;
    this.#pid = poiCollection.pid;
    this.#pois = poiCollection.pois;
    this.#radius = poiCollection.radius;
    this.#strokeColor = typeof poiCollection.strokeColor !== 'undefined' ? poiCollection.strokeColor : poiCollection.stroke_color;
    this.#strokeOpacity = typeof poiCollection.strokeOpacity !== 'undefined' ? poiCollection.strokeOpacity : poiCollection.stroke_opacity;
    this.#strokeWeight = typeof poiCollection.strokeWeight !== 'undefined' ? poiCollection.strokeWeight : poiCollection.stroke_weight;
    this.#title = poiCollection.title;
    this.#uid = poiCollection.uid;
  };

  /**
   * @returns {string}
   */
  get address() {
    return this.#address;
  }

  /**
   * @returns {string}
   */
  get categories() {
    return this.#categories;
  }

  /**
   * @returns {string}
   */
  get collectionType() {
    return this.#collectionType;
  }

  /**
   * @returns {array}
   */
  get configurationMap() {
    return this.#configurationMap;
  }

  /**
   * @returns {string}
   */
  get distance() {
    return this.#distance;
  }

  /**
   * @returns {string}
   */
  get fillColor() {
    return this.#fillColor;
  }

  /**
   * @param {string} fillColor
   */
  set fillColor(fillColor) {
    this.#fillColor = fillColor;
  }

  /**
   * @returns {string}
   */
  get fillOpacity() {
    return this.#fillOpacity;
  }

  /**
   * @param {string} fillOpacity
   */
  set fillOpacity(fillOpacity) {
    this.#fillOpacity = fillOpacity;
  }

  /**
   * @returns {string}
   */
  get foreignRecords() {
    return this.#foreignRecords;
  }

  /**
   * @returns {string}
   */
  get infoWindowContent() {
    return this.#infoWindowContent;
  }

  /**
   * @returns {string}
   */
  get latitude() {
    return this.#latitude;
  }

  /**
   * @returns {string}
   */
  get longitude() {
    return this.#longitude;
  }

  /**
   * @returns {string}
   */
  get markerIcon() {
    return this.#markerIcon;
  }

  /**
   * @returns {string}
   */
  get markerIconAnchorPosX() {
    return this.#markerIconAnchorPosX;
  }

  /**
   * @returns {string}
   */
  get markerIconAnchorPosY() {
    return this.#markerIconAnchorPosY;
  }

  /**
   * @returns {string}
   */
  get markerIconHeight() {
    return this.#markerIconHeight;
  }

  /**
   * @returns {string}
   */
  get markerIconWidth() {
    return this.#markerIconWidth;
  }

  /**
   * @returns {string}
   */
  get pid() {
    return this.#pid;
  }

  /**
   * @returns {string}
   */
  get pois() {
    return this.#pois;
  }

  /**
   * @returns {string}
   */
  get radius() {
    return this.#radius;
  }

  /**
   * @returns {string}
   */
  get strokeColor() {
    return this.#strokeColor;
  }

  /**
   * @param {string} strokeColor
   */
  set strokeColor(strokeColor) {
    this.#strokeColor = strokeColor;
  }

  /**
   * @returns {string}
   */
  get strokeOpacity() {
    return this.#strokeOpacity;
  }

  /**
   * @param {string} strokeOpacity
   */
  set strokeOpacity(strokeOpacity) {
    this.#strokeOpacity = strokeOpacity;
  }

  /**
   * @returns {string}
   */
  get strokeWeight() {
    return this.#strokeWeight;
  }

  /**
   * @param {string} strokeWeight
   */
  set strokeWeight(strokeWeight) {
    this.#strokeWeight = strokeWeight;
  }

  /**
   * @returns {string}
   */
  get title() {
    return this.#title;
  }

  /**
   * @returns {string}
   */
  get uid() {
    return this.#uid;
  }
}

export class Settings {
  #activateScrollWheel = '';
  #categories = '';
  #forceZoom = '';
  #fullScreenControl = '';
  #infoWindow = '';
  #infoWindowContentTemplatePath = '';
  #mapHeight = '';
  #mapProvider = '';
  #mapTile = '';
  #mapTileAttribution = '';
  #mapTypeControl = '';
  #mapTypeId = '';
  #mapWidth = '';
  #overlay = '';
  #poiCollection = '';
  #scaleControl = '';
  #streetViewControl = '';
  #styles = '';
  #zoom = '';
  #zoomControl = '';

  /**
   * @constructor
   * @param {object} settings
   */
  constructor(settings) {
    this.#activateScrollWheel = settings.activateScrollWheel;
    this.#categories = settings.categories;
    this.#forceZoom = settings.forceZoom;
    this.#fullScreenControl = settings.fullScreenControl;
    this.#infoWindow = settings.infoWindow;
    this.#infoWindowContentTemplatePath = settings.infoWindowContentTemplatePath;
    this.#mapHeight = settings.mapHeight;
    this.#mapProvider = settings.mapProvider;
    this.#mapTile = settings.mapTile;
    this.#mapTileAttribution = settings.mapTileAttribution;
    this.#mapTypeControl = settings.mapTypeControl;
    this.#mapTypeId = settings.mapTypeId;
    this.#mapWidth = settings.mapWidth;
    this.#overlay = settings.overlay;
    this.#poiCollection = settings.poiCollection;
    this.#scaleControl = settings.scaleControl;
    this.#streetViewControl = settings.streetViewControl;
    this.#styles = settings.styles;
    this.#zoom = settings.zoom;
    this.#zoomControl = settings.zoomControl;
  };

  /**
   * @returns {string}
   */
  get activateScrollWheel() {
    return this.#activateScrollWheel;
  }

  /**
   * @returns {string}
   */
  get categories() {
    return this.#categories;
  }

  /**
   * @returns {string}
   */
  get forceZoom() {
    return this.#forceZoom;
  }

  /**
   * @returns {string}
   */
  get fullScreenControl() {
    return this.#fullScreenControl;
  }

  /**
   * @returns {string}
   */
  get infoWindow() {
    return this.#infoWindow;
  }

  /**
   * @returns {string}
   */
  get infoWindowContentTemplatePath() {
    return this.#infoWindowContentTemplatePath;
  }

  /**
   * @returns {string}
   */
  get mapHeight() {
    return this.#mapHeight;
  }

  /**
   * @returns {string}
   */
  get mapProvider() {
    return this.#mapProvider;
  }

  /**
   * @returns {string}
   */
  get mapTile() {
    return this.#mapTile;
  }

  /**
   * @returns {string}
   */
  get mapTileAttribution() {
    return this.#mapTileAttribution;
  }

  /**
   * @returns {string}
   */
  get mapTypeControl() {
    return this.#mapTypeControl;
  }

  /**
   * @returns {string}
   */
  get mapTypeId() {
    return this.#mapTypeId;
  }

  /**
   * @returns {string}
   */
  get mapWidth() {
    return this.#mapWidth;
  }

  /**
   * @returns {string}
   */
  get overlay() {
    return this.#overlay;
  }

  /**
   * @returns {string}
   */
  get scaleControl() {
    return this.#scaleControl;
  }

  /**
   * @returns {string}
   */
  get streetViewControl() {
    return this.#streetViewControl;
  }

  /**
   * @returns {string}
   */
  get styles() {
    return this.#styles;
  }

  /**
   * @returns {string}
   */
  get zoom() {
    return this.#zoom;
  }

  /**
   * @returns {string}
   */
  get zoomControl() {
    return this.#zoomControl;
  }
}
