# TYPO3 Plugins & Routing Configuration

This document lists all registered Extbase plugins, actions, caching configs, and form engine registrations.

---

## 1. Configured Plugins

All plugins are registered in `ext_localconf.php` under the vendor `JWeiland\Maps2`:

### 1.1 `Maps2` (Main Map Plugin)
* **Extension Key:** `maps2`
* **Plugin Name:** `Maps2`
* **Controller/Action:** `PoiCollectionController::show` (Non-cacheable: None)
* **Type:** `PLUGIN_TYPE_CONTENT_ELEMENT`

### 1.2 `Overlay`
* **Extension Key:** `maps2`
* **Plugin Name:** `Overlay`
* **Controller/Action:** `PoiCollectionController::overlay` (Non-cacheable: `overlay`)
* **Type:** `PLUGIN_TYPE_CONTENT_ELEMENT`

### 1.3 `SearchWithinRadius`
* **Extension Key:** `maps2`
* **Plugin Name:** `SearchWithinRadius`
* **Controller/Action:** `PoiCollectionController::search, listRadius` (Non-cacheable: `listRadius`)
* **Type:** `PLUGIN_TYPE_CONTENT_ELEMENT`

### 1.4 `CityMap`
* **Extension Key:** `maps2`
* **Plugin Name:** `CityMap`
* **Controller/Action:** `CityMapController::show, search` (Non-cacheable: `search`)
* **Type:** `PLUGIN_TYPE_CONTENT_ELEMENT`

---

## 2. Caching & Request Handling

* **Info Window Caching:** A custom cache group `maps2_cachedhtml` is registered in `SYS/caching/cacheConfigurations` with groups `['pages', 'all']`.
* **cHash Excluded Parameters:** `tx_maps2_citymap[street]` is excluded from `FE/cacheHash` to facilitate raw GET form submissions.

---

## 3. Form Engine & Nodes

The following customized nodes are registered for the Backend Form Engine in `ext_localconf.php`:

| Node Name | Priority | Class | Purpose |
| :--- | :--- | :--- | :--- |
| `maps2InfoWindowContent` | 40 | `JWeiland\Maps2\Form\FieldInformation\InfoWindowContent` | Custom field info rendering |
| `maps2ReadOnlyInputText` | 40 | `JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement` | Custom read-only input elements |
| `maps2MapProvider` | 40 | `JWeiland\Maps2\Form\Resolver\MapProviderResolver` | Dynamic map provider selection |

---

## 4. Hooks / Signals

* **TCEmain Hook:** `JWeiland\Maps2\Hook\CreateMaps2RecordHook` is registered under `processDatamapClass` to create corresponding `maps2` records automatically when saving foreign records.
