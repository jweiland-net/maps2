# Backend-Specific Classes & Previews (`Classes/Backend/`)

This document specifies classes and workflows that are specifically designed for the TYPO3 Backend (e.g., page module previews, custom wizards, or modules).

---

## 1. Directory Structure

```text
Classes/Backend/
└── Preview/
    └── Maps2PluginPreview.php  # Renders backend previews for maps2 content elements
```

---

## 2. Page Module Preview Rendering

To enhance editor experience in the Page Module, `maps2` overrides default preview rendering for content elements using a custom preview renderer class.

### 2.1 Registration
In `Configuration/TCA/Overrides/tt_content.php`, the class is assigned as the `previewRenderer` for the respective CType types:

```php
$GLOBALS['TCA']['tt_content']['types']['maps2_maps2']['previewRenderer'] = Maps2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['maps2_searchwithinradius']['previewRenderer'] = Maps2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['maps2_citymap']['previewRenderer'] = Maps2PluginPreview::class;
```

### 2.2 Class: `JWeiland\Maps2\Backend\Preview\Maps2PluginPreview`
* **Parent Class:** `TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer`
* **Template Path:** `EXT:maps2/Resources/Private/Templates/PluginPreview/Maps2.html`
* **Dependency Injection (Constructor):**
  - `TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools`
  - `JWeiland\Maps2\Service\PoiCollectionService`
  - `TYPO3\CMS\Core\View\ViewFactoryInterface`

### 2.3 Constants & Constraints
* **`ALLOWED_PLUGINS`**: Only processes and renders previews for:
  - `maps2_maps2`
  - `maps2_citymap`
  - `maps2_searchwithinradius`

### 2.4 Rendering Workflow
The method `renderPageModulePreviewContent(GridColumnItem $item)` performs the following steps:

1. **Validation:** Checks if the `tt_content` record CType is listed in `ALLOWED_PLUGINS`. If not, returns an empty string.
2. **Template Creation:** Instantiates a standalone view using the `ViewFactoryInterface` pointing to `EXT:maps2/Resources/Private/Templates/PluginPreview/Maps2.html`.
3. **Record Variables:** Assigns the properties of the raw `tt_content` record to the view.
4. **Translated Title:** Fetches and translates the plugin name (e.g., `plugin.maps2.title`) and assigns it as `pluginName`.
5. **FlexForm Transformation:** If the record has non-empty FlexForm configurations in `pi_flexform`, converts it to a PHP array and assigns it to `pi_flexform_transformed`.
6. **POI Collection Association:** If the CType is `maps2_maps2`:
   - Checks if a valid `poiCollection` UID is set in the FlexForm settings.
   - Fetches the POI Collection record using `PoiCollectionService::findByUid`.
   - Assigns the fetched collection to `poiCollectionRecord`.
7. **Compilation:** Returns the rendered HTML string of the Fluid template.
