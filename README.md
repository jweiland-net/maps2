# TYPO3 Extension `maps2`

![Build Status](https://github.com/jweiland-net/maps2/workflows/CI/badge.svg)

maps2 is an extension for TYPO3 CMS. It can render you a map based on Google Maps and/or OpenStreetMap. If you
want, you can create Markers, Routes, Area and Radius overlays. Assign these overlays to categories to
build a map, with all points based on selected category.

## 1 Features

* Create maps with Google Maps and/or OpenStreetMap
* Create POIs, radius, area and/or paths as Overlay on to the map.

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/maps2
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `maps2` with the extension manager module.

### 2.2 Minimal setup

1) Include the static TypoScript of the extension.
2) Create care-records on a sysfolder.
3) Create a plugin on a page and select at least the sysfolder as startingpoint.
