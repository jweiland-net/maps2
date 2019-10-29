<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Update;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With maps2 5.0.0 we have moved some FlexForm Settings to another sheet.
 * To prevent duplicates in DB, this update wizard removes old settings from FlexForm.
 */
class MoveOldFlexFormSettings94Update implements UpgradeWizardInterface
{
    /**
     * @var MoveOldFlexFormSettingsUpdate
     */
    protected $flexFormUpdate;

    public function __construct(MoveOldFlexFormSettingsUpdate $flexFormUpdate = null)
    {
        if ($flexFormUpdate === null) {
            $flexFormUpdate = GeneralUtility::makeInstance(MoveOldFlexFormSettingsUpdate::class);
        }
        $this->flexFormUpdate = $flexFormUpdate;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->flexFormUpdate->getIdentifier();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->flexFormUpdate->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->flexFormUpdate->getDescription();
    }

    /**
     * @return bool
     */
    public function updateNecessary(): bool
    {
        return $this->flexFormUpdate->updateNecessary();
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        return $this->flexFormUpdate->executeUpdate();
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}
