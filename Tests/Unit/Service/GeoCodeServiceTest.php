<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Service;

use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\ClientResult;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\GoogleMapsMapper;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test GeoCode Service class
 */
class GeoCodeServiceTest extends UnitTestCase
{
    private const RESPONSE = [
        'results' => [
            0 => [
                'formatted_address' => 'My street 123, 12345 somewhere',
            ],
            1 => [
                'formatted_address' => 'Other street 1, 12345 somewhere',
            ],
        ],
    ];

    protected GeoCodeService $subject;

    protected GoogleMapsClient|MockObject $mapProviderClient;

    protected GeocodeRequest|MockObject $gmGeocodeRequestMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapProviderClient = $this->createMock(GoogleMapsClient::class);
        $this->gmGeocodeRequestMock = $this->createMock(GeocodeRequest::class);

        $this->subject = new GeoCodeService(
            $this->mapProviderClient,
            $this->gmGeocodeRequestMock,
            new GoogleMapsMapper(),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapProviderClient,
            $this->gmGeocodeRequestMock,
        );

        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    public static function emptyAddressDataProvider(): array
    {
        return [
            'empty string' => [''],
            'spaces only' => ['    '],
        ];
    }

    #[Test]
    #[DataProvider('emptyAddressDataProvider')]
    public function getGeoCodeResultWithEmptyAddressWillReturnEmptyResultWithoutError(string $address): void
    {
        $this->mapProviderClient
            ->expects($this->never())
            ->method('getResult');
        $this->mapProviderClient
            ->expects($this->never())
            ->method('processRequest');

        $result = $this->subject->getGeoCodeResult($address);

        self::assertCount(0, $result->positions);
        self::assertNull($result->error);
        self::assertFalse($result->hasError());
        self::assertNull($result->getFirstPosition());
    }

    #[Test]
    public function getGeoCodeResultWillReturnMappedPositions(): void
    {
        $this->mapProviderClient
            ->expects($this->once())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock, 'My private address')
            ->willReturn(new ClientResult(self::RESPONSE));

        $result = $this->subject->getGeoCodeResult('My private address');

        self::assertCount(2, $result->positions);
        self::assertFalse($result->hasError());
        self::assertSame(
            'My street 123, 12345 somewhere',
            $result->getFirstPosition()?->getFormattedAddress(),
        );
    }

    public static function clientErrorDataProvider(): array
    {
        $dataSets = [];
        foreach (ClientErrorEnum::cases() as $type) {
            $dataSets[$type->name] = [new ClientError($type, 'Message of ' . $type->name, 'Title')];
        }

        return $dataSets;
    }

    #[Test]
    #[DataProvider('clientErrorDataProvider')]
    public function getGeoCodeResultWillPassThroughErrorOfClient(ClientError $error): void
    {
        $this->mapProviderClient
            ->expects($this->once())
            ->method('getResult')
            ->willReturn(new ClientResult([], $error));

        $result = $this->subject->getGeoCodeResult('My private address');

        self::assertCount(0, $result->positions);
        self::assertTrue($result->hasError());
        self::assertSame($error, $result->error);
        self::assertNull($result->getFirstPosition());
    }

    #[Test]
    public function getGeoCodeResultWithClientNotExtendingAbstractClientWillUseProcessRequest(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock
            ->expects($this->once())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock, 'My private address')
            ->willReturn(self::RESPONSE);

        $subject = new GeoCodeService($clientMock, $this->gmGeocodeRequestMock, new GoogleMapsMapper());
        $result = $subject->getGeoCodeResult('My private address');

        self::assertCount(2, $result->positions);
        self::assertNull($result->error);
    }

    #[Test]
    public function getGeoCodeResultWithClientNotExtendingAbstractClientWillReturnNoErrorForEmptyResponse(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock
            ->expects($this->once())
            ->method('processRequest')
            ->willReturn([]);

        $subject = new GeoCodeService($clientMock, $this->gmGeocodeRequestMock, new GoogleMapsMapper());
        $result = $subject->getGeoCodeResult('My private address');

        self::assertCount(0, $result->positions);
        self::assertNull($result->error);
        self::assertFalse($result->hasError());
    }

    #[Test]
    #[DataProvider('emptyAddressDataProvider')]
    public function getPositionsByAddressWithEmptyAddressWillReturnEmptyObjectStorage(string $address): void
    {
        self::assertCount(
            0,
            $this->subject->getPositionsByAddress($address),
        );
    }

    #[Test]
    public function getPositionsByAddressWithEmptyResponseWillReturnEmptyObjectStorage(): void
    {
        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult());

        self::assertCount(
            0,
            $this->subject->getPositionsByAddress('test street 123, 12345 city'),
        );
    }

    #[Test]
    public function getPositionsByAddressWillReturnFilledObjectStorage(): void
    {
        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(self::RESPONSE));

        self::assertCount(
            2,
            $this->subject->getPositionsByAddress('My private address'),
        );
    }

    #[Test]
    #[DataProvider('emptyAddressDataProvider')]
    public function getFirstFoundPositionByAddressWithEmptyAddressWillReturnNull(string $address): void
    {
        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress($address),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWithErrorWillReturnNull(): void
    {
        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(
                [],
                new ClientError(ClientErrorEnum::NO_POSITIONS_FOUND, 'Nothing found', 'Error'),
            ));

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('My private address'),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWillReturnFirstPosition(): void
    {
        $expectedPosition = new Position();
        $expectedPosition->setFormattedAddress('My street 123, 12345 somewhere');

        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(self::RESPONSE));

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress('My private address'),
        );
    }
}
