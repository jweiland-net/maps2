<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Helper;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Tests\Unit\AbstractUnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test AddressHelper
 */
class AddressHelperTest extends AbstractUnitTestCase
{
    use ProphecyTrait;

    /**
     * @var AddressHelper
     */
    protected $subject;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

    protected function setUp(): void
    {
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);
        $this->subject = new AddressHelper($this->messageHelperProphecy->reveal());
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAddressWithMissingAddressColumnsKeyAddsFlashMessage(): void
    {
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('addressColumns'),
                'Key addressColumns is missing',
                FlashMessage::ERROR
            )
            ->shouldBeCalled();

        $record = [
            'uid' => 100,
            'title' => 'Market'
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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('required field'),
                'Key addressColumns is empty',
                FlashMessage::ERROR
            )
            ->shouldBeCalled();

        $record = [
            'uid' => 100,
            'title' => 'Market'
        ];
        $options = [
            'addressColumns' => []
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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('We can not find any country information within your extension'),
                'No country information found',
                FlashMessage::WARNING
            )
            ->shouldBeCalled();
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('extension manager configuration'),
                'Default country of maps2 is not configured',
                FlashMessage::WARNING
            )
            ->shouldBeCalled();

        /** @var ExtConf|ObjectProphecy $extConfProphecy */
        $extConfProphecy = $this->prophesize(ExtConf::class);
        $extConfProphecy
            ->getDefaultCountry()
            ->shouldBeCalled()
            ->willReturn('');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConfProphecy->reveal());

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Munich',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country'
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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('We can not find any country information within your extension'),
                'No country information found',
                FlashMessage::WARNING
            )
            ->shouldBeCalled();

        /** @var ExtConf|ObjectProphecy $extConfProphecy */
        $extConfProphecy = $this->prophesize(ExtConf::class);
        $extConfProphecy
            ->getDefaultCountry()
            ->shouldBeCalled()
            ->willReturn('Germany');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConfProphecy->reveal());

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Munich',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country'
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
        /** @var ExtConf|ObjectProphecy $extConfProphecy */
        $extConfProphecy = $this->prophesize(ExtConf::class);
        $extConfProphecy
            ->getDefaultCountry()
            ->shouldNotBeCalled();
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConfProphecy->reveal());

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Paris',
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'defaultCountry' => 'France'
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
        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('static_info_tables')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $this->buildAssertionForDatabaseWithReturnValue(
            'static_countries',
            [
                'cn_short_en' => 'Poland'
            ],
            [
                [
                    'expr' => 'eq',
                    'field' => 'uid',
                    'value' => 328
                ]
            ]
        );

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Warschau',
            'country' => '328'
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country'
        ];

        self::assertSame(
            'Mainstreet 17 23145 Warschau Poland',
            $this->subject->getAddress($record, $options)
        );
    }

    /**
     * @test
     */
    public function getAddressWithCountryUidWillNotFindCountryNameFromStaticCountries(): void
    {
        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('static_info_tables')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $this->buildAssertionForDatabaseWithReturnValue(
            'static_countries',
            [],
            [
                [
                    'expr' => 'eq',
                    'field' => 'uid',
                    'value' => 328
                ]
            ]
        );

        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('static_countries table'),
                'Country not found in DB',
                FlashMessage::WARNING
            )
            ->shouldBeCalled();

        $record = [
            'uid' => 100,
            'title' => 'Market',
            'street' => 'Mainstreet 17',
            'zip' => '23145',
            'city' => 'Warschau',
            'country' => '328'
        ];
        $options = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country'
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
            'countryColumn' => '    country   '
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
            'countryColumn' => '    country   '
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
            'countryColumn' => 'country'
        ];

        self::assertSame(
            'Mainstreet 23 00367 Madrid Spain',
            $this->subject->getAddress($record, $options)
        );
    }

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
     * @param string $address
     */
    public function isSameAddressWithCommaAndSpacesWillReturnTrue(string $address): void
    {
        $foreignLocationRecord = [
            'uid' => 123,
            'pid' => 321,
            'street' => 'Mainstreet',
            'zip' => '51324',
            'house_number' => '15',
            'city' => 'Cologne'
        ];
        $options = [
            'addressColumns' => [
                'street',
                'house_number',
                'zip',
                'city'
            ]
        ];
        self::assertTrue(
            $this->subject->isSameAddress($address, $foreignLocationRecord, $options)
        );
    }
}
