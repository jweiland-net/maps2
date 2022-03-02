<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MapHelper;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model for PoiCollection
 * This is the main model for markers and radius records
 */
class PoiCollection extends AbstractEntity
{
    protected ExtConf $extConf;

    protected MapHelper $mapHelper;

    protected int $sysLanguageUid = 0;

    protected int $l10nParent = 0;

    protected string $collectionType = '';

    /**
     * @Extbase\Validate("NotEmpty")
     */
    protected string $title = '';

    /**
     * JSON string containing all POIs for Area and Route
     */
    protected string $configurationMap = '';

    protected string $address = '';

    protected float $latitude = 0.0;

    protected float $longitude = 0.0;

    protected int $radius = 0;

    protected string $strokeColor = '';

    protected string $strokeOpacity = '';

    protected string $strokeWeight = '';

    protected string $fillColor = '';

    protected string $fillOpacity = '';

    protected string $infoWindowContent = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected ?ObjectStorage $infoWindowImages = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected ?ObjectStorage $markerIcons = null;

    protected int $markerIconWidth = 0;

    protected int $markerIconHeight = 0;

    protected int $markerIconAnchorPosX = 0;

    protected int $markerIconAnchorPosY = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Category>
     */
    protected ?ObjectStorage $categories = null;

    /**
     * distance
     * this is a helper var. This is not part of the db
     */
    protected float $distance = 0.0;

    /**
     * As I don't know if foreign record has a valid Domain Model, the foreign records are arrays.
     * "null" marks this property as uninitialized.
     *
     * @var array|null
     */
    protected ?array $foreignRecords = null;

    public function __construct()
    {
        $this->infoWindowImages = new ObjectStorage();
        $this->markerIcons = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * As constructor arguments will be removed while instantiation of domain models,
     * we have to add extConf with help of an inject method
     */
    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    /**
     * As constructor arguments will be removed while instantiation of domain models,
     * we have to add mapHelper with help of an inject method
     */
    public function injectMapHelper(MapHelper $mapHelper): void
    {
        $this->mapHelper = $mapHelper;
    }

    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }

    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    public function getL10nParent(): int
    {
        return $this->l10nParent;
    }

    public function setL10nParent(int $l10nParent): void
    {
        $this->l10nParent = $l10nParent;
    }

    public function getCollectionType(): string
    {
        return $this->collectionType;
    }

    public function setCollectionType(string $collectionType): void
    {
        $this->collectionType = $collectionType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getConfigurationMap(): string
    {
        return $this->configurationMap;
    }

    public function setConfigurationMap(string $configurationMap): void
    {
        $this->configurationMap = $configurationMap;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude($latitude): void
    {
        $this->latitude = (float)$latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude($longitude): void
    {
        $this->longitude = (float)$longitude;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setRadius(int $radius): void
    {
        $this->radius = $radius;
    }

    public function getStrokeColor(): string
    {
        return $this->strokeColor;
    }

    public function setStrokeColor(string $strokeColor): void
    {
        $this->strokeColor = $strokeColor;
    }

    public function getStrokeOpacity(): string
    {
        return $this->strokeOpacity;
    }

    public function setStrokeOpacity(string $strokeOpacity): void
    {
        $this->strokeOpacity = $strokeOpacity;
    }

    public function getStrokeWeight(): string
    {
        return $this->strokeWeight;
    }

    public function setStrokeWeight(string $strokeWeight): void
    {
        $this->strokeWeight = $strokeWeight;
    }

    public function getFillColor(): string
    {
        return $this->fillColor;
    }

    public function setFillColor(string $fillColor): void
    {
        $this->fillColor = $fillColor;
    }

    public function getFillOpacity(): string
    {
        return $this->fillOpacity;
    }

    public function setFillOpacity(string $fillOpacity): void
    {
        $this->fillOpacity = $fillOpacity;
    }

    public function getInfoWindowContent(): string
    {
        return $this->infoWindowContent;
    }

    public function setInfoWindowContent(string $infoWindowContent): void
    {
        $this->infoWindowContent = $infoWindowContent;
    }

    public function addCategory(Category $category): void
    {
        $this->categories->attach($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->detach($category);
    }

    /**
     * @return ObjectStorage|Category[]
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function getFirstFoundCategoryWithIcon(): ?Category
    {
        $category = null;
        foreach ($this->getCategories() as $category) {
            if ($category->getMaps2MarkerIcon()) {
                break;
            }
        }
        return $category;
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function getInfoWindowImages(): ObjectStorage
    {
        return $this->infoWindowImages;
    }

    public function setInfoWindowImages(ObjectStorage $infoWindowImages): void
    {
        $this->infoWindowImages = $infoWindowImages;
    }

    public function addInfoWindowImage(FileReference $fileReference): void
    {
        $this->infoWindowImages->attach($fileReference);
    }

    public function removeInfoWindowImage(FileReference $fileReference): void
    {
        $this->infoWindowImages->detach($fileReference);
    }

    public function getMarkerIcons(): ObjectStorage
    {
        return $this->markerIcons;
    }

    public function getMarkerIcon(): string
    {
        $markerIcon = '';
        $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
        if ($categoryWithIcon instanceof Category) {
            $markerIcon = $categoryWithIcon->getMaps2MarkerIcon();
        }

        if ($this->markerIcons->count() === 0) {
            return '';
        }

        // override markerIcon, if we have an icon defined here in PoiCollection
        $this->markerIcons->rewind();
        // only one icon is allowed, so current() will give us the first icon
        $iconReference = $this->markerIcons->current();
        if (!$iconReference instanceof FileReference) {
            return $markerIcon;
        }

        $falIconReference = $iconReference->getOriginalResource();
        if (!$falIconReference instanceof \TYPO3\CMS\Core\Resource\FileReference) {
            return $markerIcon;
        }

        $siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        // Argument deprecated with TYPO3 11.3. Remove while removing TYPO3 10 compatibility
        return $siteUrl . $falIconReference->getPublicUrl(false);
    }

    public function setMarkerIcons(ObjectStorage $markerIcons): void
    {
        $this->markerIcons = $markerIcons;
    }

    public function addMarkerIcon(FileReference $fileReference): void
    {
        $this->markerIcons->attach($fileReference);
    }

    public function removeMarkerIcon(FileReference $fileReference): void
    {
        $this->markerIcons->detach($fileReference);
    }

    public function getMarkerIconWidth(): int
    {
        // prevent using local markerIconWidth, if no markerIcon is set.
        if ($this->markerIconWidth === 0 || $this->getMarkerIcons()->count() === 0) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconWidth();
            }

            return $this->extConf->getMarkerIconWidth();
        }

        return $this->markerIconWidth;
    }

    public function setMarkerIconWidth(int $markerIconWidth): void
    {
        $this->markerIconWidth = $markerIconWidth;
    }

    public function getMarkerIconHeight(): int
    {
        // prevent using local markerIconHeight, if no markerIcon is set.
        if ($this->markerIconHeight === 0 || $this->getMarkerIcons()->count() === 0) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconHeight();
            }

            return $this->extConf->getMarkerIconHeight();
        }

        return $this->markerIconHeight;
    }

    public function setMarkerIconHeight(int $markerIconHeight): void
    {
        $this->markerIconHeight = $markerIconHeight;
    }

    public function getMarkerIconAnchorPosX(): int
    {
        // prevent using local markerIconAnchorPosX, if no markerIcon is set.
        if ($this->markerIconAnchorPosX === 0 || $this->getMarkerIcons()->count() === 0) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconAnchorPosX();
            }

            return $this->extConf->getMarkerIconAnchorPosX();
        }

        return $this->markerIconAnchorPosX;
    }

    public function setMarkerIconAnchorPosX(int $markerIconAnchorPosX): void
    {
        $this->markerIconAnchorPosX = $markerIconAnchorPosX;
    }

    public function getMarkerIconAnchorPosY(): int
    {
        // prevent using local markerIconAnchorPosY, if no markerIcon is set.
        if ($this->markerIconAnchorPosY === 0 || $this->getMarkerIcons()->count() === 0) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconAnchorPosY();
            }

            return $this->extConf->getMarkerIconAnchorPosY();
        }

        return $this->markerIconAnchorPosY;
    }

    public function setMarkerIconAnchorPosY(int $markerIconAnchorPosY): void
    {
        $this->markerIconAnchorPosY = $markerIconAnchorPosY;
    }

    public function getPois(): array
    {
        $configurationMap = $this->getConfigurationMap();
        if ($configurationMap === '' || $configurationMap === null) {
            $configurationMap = '[]';
        }

        return $this->mapHelper->convertPoisAsJsonToArray($configurationMap);
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }

    public function getForeignRecords(): array
    {
        if ($this->foreignRecords === null) {
            $this->foreignRecords = [];
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $mapService->addForeignRecordsToPoiCollection($this);
        }
        return $this->foreignRecords;
    }

    public function setForeignRecords(array $foreignRecords): void
    {
        foreach ($foreignRecords as $foreignRecord) {
            $this->addForeignRecord($foreignRecord);
        }
    }

    public function addForeignRecord(array $foreignRecord): void
    {
        if ($foreignRecord !== []) {
            $this->foreignRecords[$foreignRecord['uid']] = $foreignRecord;
        }
    }

    public function removeForeignRecord(array $foreignRecord): void
    {
        unset($this->foreignRecords[$foreignRecord['uid']]);
    }
}
