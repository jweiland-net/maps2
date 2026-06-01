# Database Schema & Extbase Models

This document specifies the database structure, table mappings, and TCA configurations used in `maps2`.

---

## 1. Database Tables

The custom tables are registered in `ext_tables.sql`:

### 1.1 `tx_maps2_domain_model_poicollection`
Stores the points of interest (POI), maps, markers, areas, routes, or radii.

| Field | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `configuration_map` | `text` | `NULL` | Contains serialized/JSON map configurations (markers, options, zoom, center). |
| `latitude` | `numeric(11, 6)` | `0.000000` | Coordinates: Latitude of the center/marker. |
| `longitude` | `numeric(11, 6)` | `0.000000` | Coordinates: Longitude of the center/marker. |

---

## 2. Domain Models (Extbase)

All domain models are located under `Classes/Domain/Model/`:
* **Main Model:** `JWeiland\Maps2\Domain\Model\PoiCollection` mapped to table `tx_maps2_domain_model_poicollection`.

---

## 3. TCA (Table Configuration Array)

TCA files are organized according to modern TYPO3 v12/v13 standards inside `Configuration/TCA/`:

### 3.1 Main Table Definitions
* **`tx_maps2_domain_model_poicollection.php`**: Standard TCA definition providing the full column config and backend layout for managing maps/POIs.

### 3.2 Overrides (`Configuration/TCA/Overrides/`)
These extend existing TYPO3 system tables or other extensions:
* **`sys_category.php`**: Overrides system categories to allow grouping POIs or custom mapping classifications.
* **`tt_content.php`**: Registers content element plugins and overrides plugin options in the Page module.
* **`tx_maps2_domain_model_poicollection.php`**: Overrides/extends the core `poicollection` fields with specific custom configurations on-the-fly.
