<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Google Maps Client class
 */
class GoogleMapsClientTest extends FunctionalTestCase
{
    protected GoogleMapsClient $subject;

    protected ExtConf $extConf;

    protected MessageHelper|MockObject $messageHelperMock;

    protected RequestFactory|MockObject $requestFactoryMock;

    protected array $coreExtensionsToLoad = [
        'extensionmanager',
        'reactions',
    ];

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/maps2',
        'jweiland/events2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->subject = new GoogleMapsClient(
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function processRequestWithEmptyUriAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('');

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'URI is empty or contains invalid chars.',
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest),
        );
    }

    #[Test]
    public function processRequestWithInvalidRequestAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('https://www.jweiländ.net');

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'URI is empty or contains invalid chars.',
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest),
        );
    }

    #[Test]
    public function processRequestWillAddFlashMessageBecauseOfClientError(): void
    {
        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=Filderstadt&key=ApiKey',
            )
            ->willReturn(new HtmlResponse('Client Error', 500));

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'MapProvider returns a response with a status code different than 200',
                'Client Error',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($this->createGeocodeRequest('Filderstadt')),
        );
    }

    #[Test]
    public function processRequestWillAddFlashMessageBecauseOfZeroResults(): void
    {
        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'ZERO_RESULTS'], 200));

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'Google Maps can\'t find any position. Please check your input and try again',
                'No positions found',
                ContextualFeedbackSeverity::ERROR,
            );

        // The response is empty, even if the flash message queue is mocked
        self::assertSame(
            [],
            $this->subject->processRequest($this->createGeocodeRequest('Filderstadt')),
        );
    }

    #[Test]
    public function processRequestWillRawUrlEncodeAddress(): void
    {
        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=K%C3%B6ln%20Bonn&key=ApiKey',
            )
            ->willReturn(new JsonResponse(['status' => 'OK'], 200));

        self::assertSame(
            ['status' => 'OK'],
            $this->subject->processRequest($this->createGeocodeRequest('Köln Bonn')),
        );
    }

    #[Test]
    public function processRequestWillAddDefaultCountryForZipAddress(): void
    {
        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=12345%20Germany&key=ApiKey',
            )
            ->willReturn(new JsonResponse(['status' => 'OK'], 200));

        self::assertSame(
            ['status' => 'OK'],
            $this->subject->processRequest($this->createGeocodeRequest('12345')),
        );
    }

    #[Test]
    public function getResultWithStatusOkWillReturnResponseWithoutError(): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'OK', 'results' => []], 200));

        $this->messageHelperMock
            ->expects(self::never())
            ->method('addFlashMessage');

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame(
            ['status' => 'OK', 'results' => []],
            $result->response,
        );
        self::assertNull($result->error);
        self::assertFalse($result->hasError());
    }

    #[Test]
    public function getResultWithZeroResultsWillReturnNoPositionsFoundError(): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'ZERO_RESULTS'], 200));

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                'Google Maps can\'t find any position. Please check your input and try again',
                'No positions found',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame([], $result->response);
        self::assertTrue($result->hasError());
        self::assertEquals(
            new ClientError(
                ClientErrorEnum::NO_POSITIONS_FOUND,
                'Google Maps can\'t find any position. Please check your input and try again',
                'No positions found',
            ),
            $result->error,
        );
    }

    public static function googleErrorStatusDataProvider(): array
    {
        return [
            'REQUEST_DENIED' => ['REQUEST_DENIED', ClientErrorEnum::REQUEST_DENIED],
            'OVER_QUERY_LIMIT' => ['OVER_QUERY_LIMIT', ClientErrorEnum::QUOTA_EXCEEDED],
            'OVER_DAILY_LIMIT' => ['OVER_DAILY_LIMIT', ClientErrorEnum::QUOTA_EXCEEDED],
            'INVALID_REQUEST' => ['INVALID_REQUEST', ClientErrorEnum::INVALID_REQUEST],
            'UNKNOWN_ERROR' => ['UNKNOWN_ERROR', ClientErrorEnum::SERVICE_ERROR],
            'unknown status' => ['FOO_BAR', ClientErrorEnum::SERVICE_ERROR],
        ];
    }

    #[Test]
    #[DataProvider('googleErrorStatusDataProvider')]
    public function getResultWithErrorStatusWillReturnError(string $status, ClientErrorEnum $expectedType): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(
                [
                    'status' => $status,
                    'error_message' => 'Message of Google Maps',
                ],
                200,
            ));

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                'Message of Google Maps',
                'Error',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError($expectedType, 'Message of Google Maps', 'Error'),
            $result->error,
        );
    }

    #[Test]
    #[DataProvider('googleErrorStatusDataProvider')]
    public function getResultWithErrorStatusWithoutErrorMessageWillUseStatusAsMessage(
        string $status,
        ClientErrorEnum $expectedType,
    ): void {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => $status], 200));

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                $status,
                'Error',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertEquals(
            new ClientError($expectedType, $status, 'Error'),
            $this->subject->getResult($this->createGeocodeRequest('Filderstadt'))->error,
        );
    }

    public static function invalidJsonDataProvider(): array
    {
        return [
            'HTML instead of JSON' => ['<html lang="en"><body>Error</body></html>'],
            'empty body' => [''],
            'broken JSON' => ['{"status": "OK"'],
            'JSON string instead of object' => ['"OK"'],
            'JSON number instead of object' => ['123'],
        ];
    }

    #[Test]
    #[DataProvider('invalidJsonDataProvider')]
    public function getResultWithInvalidJsonWillReturnInvalidResponseError(string $body): void
    {
        $response = new Response();
        $response->getBody()->write($body);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn($response);

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                'The response of Google Maps was not a valid JSON response.',
                'Invalid JSON response',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError(
                ClientErrorEnum::INVALID_RESPONSE,
                'The response of Google Maps was not a valid JSON response.',
                'Invalid JSON response',
            ),
            $result->error,
        );
    }

    private function createGeocodeRequest(string $address): GeocodeRequest
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            defaultCountry: 'Germany',
            googleMapsGeocodeApiKey: 'ApiKey',
        ));
        $geocodeRequest->addParameter('address', $address);

        return $geocodeRequest;
    }
}
