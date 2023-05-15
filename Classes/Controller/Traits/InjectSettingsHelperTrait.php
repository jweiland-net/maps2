<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller\Traits;

use JWeiland\Maps2\Helper\SettingsHelper;

trait InjectSettingsHelperTrait
{
    protected SettingsHelper $settingsHelper;

    public function injectSettingsHelper(SettingsHelper $settingsHelper): void
    {
        $this->settingsHelper = $settingsHelper;
    }
}
