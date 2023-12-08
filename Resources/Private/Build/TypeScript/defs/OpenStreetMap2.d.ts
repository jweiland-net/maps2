interface ContentRecord {
  CType: string;
  bodytext?: string;
  colPos: number;
  crdate: number;
  date: number;
  endtime: number;
  frame_class: string;
  header: string;
  hidden: boolean;
  imageheight: number;
  imagewidth: number;
  list_type: string;
  pages: string;
  pid: number;
  starttime: number;
  subheader: string;
  sys_language_uid: number;
  target: string;
  tstamp: number;
  uid: number;
}

interface Category {
  uid: number;
  title: string;
}

interface PoiCollection {
  address: string;
  categories: Category[];
  collectionType: string;
  distance: number;
  fillColor: string;
  fillOpacity: number;
  foreignRecords: any[];
  infoWindowContent: string;
  latitude: number;
  longitude: number;
  markerIcon: string;
  markerIconAnchorPosX: number;
  markerIconAnchorPosY: number;
  markerIconHeight: number;
  markerIconWidth: number;
  pid: number;
  pois: any[];
  radius: number;
  strokeColor: string;
  strokeOpacity: number;
  strokeWeight: number;
  title: string;
  uid: number;
}

interface Image {
  height: number;
  width: number;
}

interface InfoWindow {
  image: Image;
}

interface Link {
  addSection: boolean;
}

interface Overlay {
  link: Link;
}

interface Settings {
  activateScrollWheel: string;
  categories: string;
  forceZoom: boolean;
  fullScreenControl: boolean;
  infoWindow: InfoWindow;
  infoWindowContentTemplatePath: string;
  mapHeight: string;
  mapProvider: string;
  mapTile: string;
  mapTileAttribution: string;
  mapTypeControl: string;
  mapTypeId: string;
  mapWidth: string;
  overlay: Overlay;
  poiCollection: string;
  scaleControl: boolean;
  streetViewControl: boolean;
  styles: string;
  zoom: number;
  zoomControl: boolean;
}

interface ExtConf {
  defaultCountry: string;
  defaultLatitude: number;
  defaultLongitude: number;
  defaultMapProvider: string;
  defaultMapType: string;
  defaultRadius: number;
  explicitAllowMapProviderRequests: boolean;
  explicitAllowMapProviderRequestsBySessionOnly: boolean;
  fillColor: string;
  fillOpacity: number;
  googleMapsGeocodeApiKey: string;
  googleMapsGeocodeUri: string;
  googleMapsJavaScriptApiKey: string;
  googleMapsLibrary: string;
  infoWindowContentTemplatePath: string;
  mapProvider: string;
  markerIconAnchorPosX: number;
  markerIconAnchorPosY: number;
  markerIconHeight: number;
  markerIconWidth: number;
  openStreetMapGeocodeUri: string;
  strokeColor: string;
  strokeOpacity: number;
  strokeWeight: number;
}

interface Environment {
  ajaxUrl: string;
  contentRecord: ContentRecord;
  extConf: ExtConf;
  settings: Settings;
  lat: number;
  lng: number;
}
