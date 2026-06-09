<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Helper;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Tca\ColumnRegistration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test AddressHelper
 */
class AddressHelperTest extends FunctionalTestCase
{
    protected AddressHelper $subject;

    protected MessageHelper|MockObject $messageHelperMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageHelperMock = $this->createMock(MessageHelper::class);

        $this->subject = new AddressHelper(
            $this->messageHelperMock,
            $this->get(CountryProvider::class),
            new ExtConf(),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function getAddressWithoutCountryButWithMaps2RegistryFallbackGeneratesNoFlashMessage(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Paris',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            defaultCountry: 'France',
        );

        self::assertSame(
            'Mainstreet 17 23145 Paris France',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    #[Test]
    public function getAddressWith2IsoCodeWillGetCountryName(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Filderstadt',
            'country' => 'de',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            countryColumn: 'country',
        );

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    #[Test]
    public function getAddressWith3IsoCodeWillGetCountryName(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Filderstadt',
            'country' => 'deu',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            countryColumn: 'country',
        );

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    #[Test]
    public function getAddressWithEnglishNameWillGetCountryName(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Filderstadt',
            'country' => 'Germany',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            countryColumn: 'country',
        );

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    #[Test]
    public function getAddressWillUnifyMaps2RegistryOptions(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => ' Mainstreet 17  ',
            'zip' => '  23145  ',
            'city' => '     Madrid  ',
            'country' => '  Spain   ',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            countryColumn: '    country   ',
        );

        self::assertSame(
            'Mainstreet 17 23145 Madrid Spain',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    #[Test]
    public function getAddressWillRemoveCountryFromAddressColumnsIfAvailable(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => ' Mainstreet 17  ',
            'zip' => '  23145  ',
            'city' => '     Madrid  ',
            'country' => '  Spain   ',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city', 'country'],
            countryColumn: '    country   ',
        );

        self::assertSame(
            'Mainstreet 17 23145 Madrid Spain',
            $this->subject->getAddress($record, $columnRegistration),
        );
    }

    /**
     * @return array<string, array<string>>
     */
    public static function addressDataProvider(): array
    {
        return [
            'address with commas and spaces' => ['Mainstreet 15, 51324 Cologne, Germany'],
            'address with and spaces' => ['Mainstreet 15 51324 Cologne Germany'],
            'address without country' => ['Mainstreet 15, 51324 Cologne'],
            'address with different position' => ['15 Cologne 51324 Germany Mainstreet'],
            'address with lower cased values' => ['15 cologne 51324 germany mainstreet'],
        ];
    }

    #[Test]
    #[DataProvider('addressDataProvider')]
    public function isSameAddressWithCommaAndSpacesWillReturnTrue(string $address): void
    {
        $foreignLocationRecord = [
            'uid' => 123,
            'pid' => 321,
            'street' => 'Mainstreet',
            'zip' => '51324',
            'house_number' => '15',
            'city' => 'Cologne',
        ];
        $columnRegistration = new ColumnRegistration(
            tableName: 'tt_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'house_number', 'zip', 'city'],
            countryColumn: '    country   ',
        );

        self::assertTrue(
            $this->subject->isSameAddress($address, $foreignLocationRecord, $columnRegistration),
        );
    }
}
