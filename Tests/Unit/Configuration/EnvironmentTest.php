<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Configuration;

use JWeiland\Maps2\Configuration\Environment;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EnvironmentTest
 */
class EnvironmentTest extends UnitTestCase
{
    #[Test]
    public function gettersAndJsonSerializeReturnExpectedValues(): void
    {
        $settings = ['mapProvider' => 'gm'];
        $extConf = ['defaultLatitude' => 50.0];
        $contentRecord = ['uid' => 42];
        $ajaxUrl = '/ajax-url';
        $id = 123;

        $subject = new Environment(
            $settings,
            $extConf,
            $contentRecord,
            $ajaxUrl,
            $id,
        );

        self::assertSame($settings, $subject->getSettings());
        self::assertSame($extConf, $subject->getExtConf());
        self::assertSame($contentRecord, $subject->getContentRecord());
        self::assertSame($ajaxUrl, $subject->getAjaxUrl());
        self::assertSame($id, $subject->getId());

        $expectedJson = json_encode([
            'settings' => $settings,
            'extConf' => $extConf,
            'contentRecord' => $contentRecord,
            'ajaxUrl' => $ajaxUrl,
            'id' => $id,
        ], JSON_THROW_ON_ERROR);

        self::assertSame($expectedJson, json_encode($subject, JSON_THROW_ON_ERROR));
    }
}
