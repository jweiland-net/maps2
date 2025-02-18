<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Update;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * With TYPO3 13 all plugins have to be declared as content elements (CType) insteadof "list_type"
 */
#[UpgradeWizard('maps2_migratePluginsToContentElementsUpdate')]
class MigratePluginToContentElementUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'maps2_maps2' => 'maps2_maps2',
            'maps2_overlay' => 'maps2_overlay',
            'maps2_searchwithinradius' => 'maps2_searchwithinradius',
            'maps2_citymap' => 'maps2_citymap',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrate plugins to Content Elements';
    }

    public function getDescription(): string
    {
        return 'The modern way to register plugins for TYPO3 is to register them as content element types. ' .
            'Running this wizard will migrate all maps2 plugins to content element (CType)';
    }
}
