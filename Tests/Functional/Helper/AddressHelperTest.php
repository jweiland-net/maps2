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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
    public function getAddressWithMissingAddressColumnsKeyAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects($this->atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('addressColumns'),
                'Key addressColumns is missing',
                ContextualFeedbackSeverity::ERROR,
            );

        $record = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            '',
            $this->subject->getAddress($record, $options),
        );
    }

    #[Test]
    public function getAddressWithEmptyAddressColumnsAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects($this->atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('required field'),
                'Key addressColumns is empty',
                ContextualFeedbackSeverity::ERROR,
            );

        $record = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [
            'addressColumns' => [],
        ];

        self::assertSame(
            '',
            $this->subject->getAddress($record, $options),
        );
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'defaultCountry' => 'France',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Paris France',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => '    country   ',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Madrid Spain',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => ['street', 'zip', 'city', 'country'],
            'countryColumn' => '    country   ',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Madrid Spain',
            $this->subject->getAddress($record, $options),
        );
    }

    #[Test]
    public function getAddressWillConvertCommaSeparatedAddressColumnsIntoArray(): void
    {
        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet',
            'house_number' => '23',
            'zip' => '00367',
            'city' => 'Madrid',
            'country' => '  Spain   ',
        ];
        $options = [
            'addressColumns' => 'street, house_number, zip, city',
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 23 00367 Madrid Spain',
            $this->subject->getAddress($record, $options),
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
        $options = [
            'addressColumns' => [
                'street',
                'house_number',
                'zip',
                'city',
            ],
        ];

        self::assertTrue(
            $this->subject->isSameAddress($address, $foreignLocationRecord, $options),
        );
    }
}
