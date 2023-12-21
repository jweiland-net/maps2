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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test AddressHelper
 */
class AddressHelperTest extends FunctionalTestCase
{
    protected AddressHelper $subject;

    /**
     * @var MessageHelper|MockObject
     */
    protected $messageHelperMock;

    /**
     * @var ExtConf
     */
    protected $extConf;

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new AddressHelper(
            $this->messageHelperMock,
            $this->extConf
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperMock,
            $this->extConf
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAddressWithMissingAddressColumnsKeyAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('addressColumns'),
                'Key addressColumns is missing',
                ContextualFeedbackSeverity::ERROR
            );

        $record = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            '',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithEmptyAddressColumnsAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('required field'),
                'Key addressColumns is empty',
                ContextualFeedbackSeverity::ERROR
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
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithoutCountryAndNoFallbackGeneratesTwoFlashMessages(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->willReturnMap([
                [
                    self::stringContains('We can not find any country information within your extension'),
                    'No country information found',
                    ContextualFeedbackSeverity::WARNING,
                ],
                [
                    self::stringContains('extension manager configuration'),
                    'Default country of maps2 is not configured',
                    ContextualFeedbackSeverity::WARNING,
                ],
            ]);

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
            );

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Munich',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Munich',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithoutCountryButWithMaps2FallbackGeneratesOneFlashMessages(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('We can not find any country information within your extension'),
                'No country information found',
                ContextualFeedbackSeverity::WARNING
            );

        $this->extConf->setDefaultCountry('Germany');

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Munich',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Munich Germany',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
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
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithCountryUidWillGetCountryNameFromStaticCountries(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/static_countries.csv');

        $this->messageHelperMock
            ->expects(self::never())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('We can not find any country information within your extension'),
                'No country information found',
                ContextualFeedbackSeverity::WARNING
            );

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->with('static_info_tables')
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Filderstadt',
            'country' => '54',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Filderstadt Germany',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithCountryUidWillNotFindCountryNameFromStaticCountries(): void
    {
        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->with('static_info_tables')
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('static_countries table'),
                'Country not found in DB',
                ContextualFeedbackSeverity::WARNING
            );

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Warschau',
            'country' => '328',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
        ];

        self::assertSame(
            'Mainstreet 17 23145 Warschau',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
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
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
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
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
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
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @return array<string, array<string>>
     */
    public function addressDataProvider(): array
    {
        return [
            'address with commas and spaces' => ['Mainstreet 15, 51324 Cologne, Germany'],
            'address with and spaces' => ['Mainstreet 15 51324 Cologne Germany'],
            'address without country' => ['Mainstreet 15, 51324 Cologne'],
            'address with different position' => ['15 Cologne 51324 Germany Mainstreet'],
            'address with lower cased values' => ['15 cologne 51324 germany mainstreet'],
        ];
    }

    /**
     * @test
     * @dataProvider addressDataProvider
     */
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
            $this->subject->isSameAddress($address, $foreignLocationRecord, $options)
        );
    }
}
