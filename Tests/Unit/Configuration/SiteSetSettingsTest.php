<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class SiteSetSettingsTest
 */
class SiteSetSettingsTest extends UnitTestCase
{
    #[Test]
    public function defaultInfoWindowContentTemplateExists(): void
    {
        $extensionPath = dirname(__DIR__, 3) . '/';
        $definitions = Yaml::parseFile($extensionPath . 'Configuration/Sets/Maps2/settings.definitions.yaml');
        $templatePath = $definitions['settings']['maps2.infoWindowContent.templatePath']['default'];

        self::assertStringStartsWith('EXT:maps2/', $templatePath);
        self::assertFileExists($extensionPath . substr($templatePath, strlen('EXT:maps2/')));
    }
}
