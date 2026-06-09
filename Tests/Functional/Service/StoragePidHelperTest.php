<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Service;

use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Tca\ColumnRegistration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test StoragePidHelper
 */
class StoragePidHelperTest extends FunctionalTestCase
{
    protected StoragePidHelper $subject;

    protected MessageHelper|MockObject $messageHelperMock;

    protected ExtensionConfiguration|MockObject $extensionConfigurationMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);

        $this->subject = new StoragePidHelper(
            $this->messageHelperMock,
            $this->extensionConfigurationMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperMock,
            $this->extensionConfigurationMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function getStoragePidWithEmptyParametersWillReturnZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getDefaultStoragePidForNewPoiCollection(
                [],
                new ColumnRegistration(
                    tableName: 'tt_address',
                    columnName: 'tx_maps2_uid',
                    addressColumns: [],
                ),
            ),
        );
    }

    #[Test]
    public function getStoragePidWithInvalidParametersRecordPidWillReturnZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getDefaultStoragePidForNewPoiCollection(
                [
                    'pid' => 'ten',
                ],
                new ColumnRegistration(
                    tableName: 'tt_address',
                    columnName: 'tx_maps2_uid',
                    addressColumns: [],
                ),
            ),
        );
    }

    #[Test]
    public function getStoragePidWithRecordPidWillReturnRecordPid(): void
    {
        self::assertSame(
            12,
            $this->subject->getDefaultStoragePidForNewPoiCollection(
                [
                    'pid' => 12,
                ],
                new ColumnRegistration(
                    tableName: 'tt_address',
                    columnName: 'tx_maps2_uid',
                    addressColumns: [],
                ),
            ),
        );
    }

    #[Test]
    public function getStoragePidWithDefaultStoragePidWillOverridePid(): void
    {
        self::assertSame(
            24,
            $this->subject->getDefaultStoragePidForNewPoiCollection(
                [
                    'pid' => 12,
                ],
                new ColumnRegistration(
                    tableName: 'tt_address',
                    columnName: 'tx_maps2_uid',
                    addressColumns: [],
                    defaultStoragePid: 24,
                ),
            ),
        );
    }
}
