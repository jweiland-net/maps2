<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Service;

use JWeiland\Maps2\Client;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\ClientResult;
use JWeiland\Maps2\Client\Request;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\GoogleMapsMapper;
use JWeiland\Maps2\Mapper\MapperFactory;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test GeoCode Service class
 */
class GeoCodeServiceTest extends UnitTestCase
{
    private const ADDRESS = 'My private address';

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

    protected Client\ClientFactory|MockObject $clientFactoryMock;

    protected Client\GoogleMapsClient|MockObject $googleMapsClientMock;

    protected Request\RequestFactory|MockObject $requestFactoryMock;

    protected MapperFactory|MockObject $mapperFactoryMock;

    protected Request\GoogleMaps\GeocodeRequest|MockObject $gmGeocodeRequestMock;

    protected GeoCodeService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientFactoryMock = $this->createMock(Client\ClientFactory::class);
        $this->googleMapsClientMock = $this->createMock(Client\GoogleMapsClient::class);
        $this->requestFactoryMock = $this->createMock(Request\RequestFactory::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
        $this->gmGeocodeRequestMock = $this->createMock(Request\GoogleMaps\GeocodeRequest::class);

        $this->clientFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturn($this->googleMapsClientMock);

        $this->mapperFactoryMock
            ->method('create')
            ->willReturn(new GoogleMapsMapper());

        $this->subject = new GeoCodeService(
            $this->clientFactoryMock,
            $this->requestFactoryMock,
            $this->mapperFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->clientFactoryMock,
            $this->googleMapsClientMock,
            $this->requestFactoryMock,
            $this->mapperFactoryMock,
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
        $this->requestFactoryMock
            ->expects(self::never())
            ->method('create');
        $this->googleMapsClientMock
            ->expects(self::never())
            ->method('getResult');
        $this->googleMapsClientMock
            ->expects(self::never())
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
        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::once())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(self::RESPONSE));

        $result = $this->subject->getGeoCodeResult(self::ADDRESS);

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
        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::once())
            ->method('getResult')
            ->willReturn(new ClientResult([], $error));

        $this->mapperFactoryMock
            ->expects(self::never())
            ->method('create');

        $result = $this->subject->getGeoCodeResult(self::ADDRESS);

        self::assertCount(0, $result->positions);
        self::assertTrue($result->hasError());
        self::assertSame($error, $result->error);
        self::assertNull($result->getFirstPosition());
    }

    #[Test]
    public function getGeoCodeResultWithClientNotExtendingAbstractClientWillUseProcessRequest(): void
    {
        $this->expectGeocodeRequest();

        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock
            ->expects(self::once())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(self::RESPONSE);

        $result = $this->createSubjectWithClient($clientMock)->getGeoCodeResult(self::ADDRESS);

        self::assertCount(2, $result->positions);
        self::assertNull($result->error);
    }

    #[Test]
    public function getGeoCodeResultWithClientNotExtendingAbstractClientWillReturnNoErrorForEmptyResponse(): void
    {
        $this->expectGeocodeRequest();

        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock
            ->expects(self::once())
            ->method('processRequest')
            ->willReturn([]);

        $result = $this->createSubjectWithClient($clientMock)->getGeoCodeResult(self::ADDRESS);

        self::assertCount(0, $result->positions);
        self::assertNull($result->error);
        self::assertFalse($result->hasError());
    }

    #[Test]
    #[DataProvider('emptyAddressDataProvider')]
    public function getPositionsByAddressWithEmptyAddressWillReturnEmptyObjectStorage(string $address): void
    {
        $positions = $this->subject->getPositionsByAddress($address);

        self::assertInstanceOf(ObjectStorage::class, $positions);
        self::assertCount(0, $positions);
    }

    #[Test]
    public function getPositionsByAddressWithEmptyResponseWillReturnEmptyObjectStorage(): void
    {
        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult());

        self::assertCount(
            0,
            $this->subject->getPositionsByAddress(self::ADDRESS),
        );
    }

    #[Test]
    public function getPositionsByAddressWillReturnFilledObjectStorage(): void
    {
        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(self::RESPONSE));

        self::assertCount(
            2,
            $this->subject->getPositionsByAddress(self::ADDRESS),
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
        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(
                [],
                new ClientError(ClientErrorEnum::NO_POSITIONS_FOUND, 'Nothing found', 'Error'),
            ));

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress(self::ADDRESS),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWillReturnFirstPosition(): void
    {
        $expectedPosition = new Position();
        $expectedPosition->setFormattedAddress('My street 123, 12345 somewhere');

        $this->expectGeocodeRequest();

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('getResult')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn(new ClientResult(self::RESPONSE));

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress(self::ADDRESS),
        );
    }

    private function expectGeocodeRequest(): void
    {
        $this->gmGeocodeRequestMock
            ->expects(self::atLeastOnce())
            ->method('addParameter')
            ->with('address', self::ADDRESS);

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);
    }

    private function createSubjectWithClient(ClientInterface $client): GeoCodeService
    {
        $clientFactoryMock = $this->createMock(Client\ClientFactory::class);
        $clientFactoryMock
            ->expects(self::once())
            ->method('create')
            ->willReturn($client);

        return new GeoCodeService(
            $clientFactoryMock,
            $this->requestFactoryMock,
            $this->mapperFactoryMock,
        );
    }
}
