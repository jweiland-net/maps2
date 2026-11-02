<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use JWeiland\Maps2\Client\AbstractClient;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\AbstractRequest;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest as GoogleMapsGeocodeRequest;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest as OpenStreetMapGeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Tests\Functional\Client\Fixtures\LegacyClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test the provider independent error handling of AbstractClient
 * (request validation, HTTP status codes, transport errors, statelessness, BC).
 */
class AbstractClientTest extends FunctionalTestCase
{
    private const API_KEY = 'SecretApiKey4711';

    private const HTTP_ERROR_MESSAGE = 'MapProvider returns a response with a status code different than 200';

    protected MessageHelper|MockObject $messageHelperMock;

    protected RequestFactory|MockObject $requestFactoryMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );

        parent::tearDown();
    }

    public static function clientDataProvider(): array
    {
        return [
            'Google Maps' => [GoogleMapsClient::class],
            'Open Street Map' => [OpenStreetMapClient::class],
        ];
    }

    public static function httpStatusCodeDataProvider(): array
    {
        $statusCodes = [
            400 => ClientErrorEnum::INVALID_REQUEST,
            401 => ClientErrorEnum::REQUEST_DENIED,
            403 => ClientErrorEnum::REQUEST_DENIED,
            404 => ClientErrorEnum::SERVICE_ERROR,
            429 => ClientErrorEnum::QUOTA_EXCEEDED,
            500 => ClientErrorEnum::SERVICE_ERROR,
            503 => ClientErrorEnum::SERVICE_ERROR,
        ];

        $dataSets = [];
        foreach (self::clientDataProvider() as $clientLabel => [$clientClassName]) {
            foreach ($statusCodes as $statusCode => $expectedType) {
                $dataSets[$clientLabel . ' ' . $statusCode] = [$clientClassName, $statusCode, $expectedType];
            }
        }

        return $dataSets;
    }

    public static function invalidUriDataProvider(): array
    {
        $uris = [
            'empty URI' => '',
            'URI with umlaut' => 'https://www.jweiländ.net',
            'URI with whitespace and API key' => 'https://maps.googleapis.com/maps/api/geocode/json?address=Filder stadt&key=' . self::API_KEY,
        ];

        $dataSets = [];
        foreach (self::clientDataProvider() as $clientLabel => [$clientClassName]) {
            foreach ($uris as $uriLabel => $uri) {
                $dataSets[$clientLabel . ' ' . $uriLabel] = [$clientClassName, $uri];
            }
        }

        return $dataSets;
    }

    #[Test]
    #[DataProvider('invalidUriDataProvider')]
    public function getResultWithInvalidRequestWillReturnInvalidRequestError(string $clientClassName, string $uri): void
    {
        $subject = $this->createClient($clientClassName);
        $request = $this->createGeocodeRequest($clientClassName);
        $request->setUri($uri);

        $this->requestFactoryMock
            ->expects(self::never())
            ->method('request');

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                'URI is empty or contains invalid chars.',
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $subject->getResult($request);

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError(
                ClientErrorEnum::INVALID_REQUEST,
                'URI is empty or contains invalid chars.',
                'Invalid request URI',
            ),
            $result->error,
        );
    }

    #[Test]
    #[DataProvider('invalidUriDataProvider')]
    public function processRequestWithInvalidRequestWillReturnEmptyArray(string $clientClassName, string $uri): void
    {
        $subject = $this->createClient($clientClassName);
        $request = $this->createGeocodeRequest($clientClassName);
        $request->setUri($uri);

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage');

        self::assertSame([], $subject->processRequest($request));
    }

    #[Test]
    #[DataProvider('httpStatusCodeDataProvider')]
    public function getResultWithNon200ResponseWillReturnError(
        string $clientClassName,
        int $statusCode,
        ClientErrorEnum $expectedType,
    ): void {
        $subject = $this->createClient($clientClassName);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'OK'], $statusCode));

        $this->expectHttpErrorFlashMessage();

        $result = $subject->getResult($this->createGeocodeRequest($clientClassName));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError($expectedType, self::HTTP_ERROR_MESSAGE, 'Client Error'),
            $result->error,
        );
    }

    #[Test]
    #[DataProvider('httpStatusCodeDataProvider')]
    public function getResultWithRequestExceptionContainingResponseWillReturnError(
        string $clientClassName,
        int $statusCode,
        ClientErrorEnum $expectedType,
    ): void {
        $subject = $this->createClient($clientClassName);

        // Same as Guzzle's http_errors middleware: ClientException for 4xx, ServerException for 5xx
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willThrowException(RequestException::create(
                new GuzzleRequest('GET', $this->getRequestUri($clientClassName)),
                new GuzzleResponse($statusCode, [], '{"error_message": "Do not show me"}'),
            ));

        $this->expectHttpErrorFlashMessage();

        $result = $subject->getResult($this->createGeocodeRequest($clientClassName));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError($expectedType, self::HTTP_ERROR_MESSAGE, 'Client Error'),
            $result->error,
        );
    }

    #[Test]
    #[DataProvider('httpStatusCodeDataProvider')]
    public function processRequestWithNon200ResponseWillReturnEmptyArray(
        string $clientClassName,
        int $statusCode,
        ClientErrorEnum $expectedType,
    ): void {
        $subject = $this->createClient($clientClassName);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'OK'], $statusCode));

        $this->expectHttpErrorFlashMessage();

        self::assertSame(
            [],
            $subject->processRequest($this->createGeocodeRequest($clientClassName)),
        );
    }

    public static function transportExceptionDataProvider(): array
    {
        $exceptions = [
            'ConnectException' => static fn(string $uri): GuzzleException => new ConnectException(
                'cURL error 6: Could not resolve host (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for ' . $uri,
                new GuzzleRequest('GET', $uri),
            ),
            'RequestException without response' => static fn(string $uri): GuzzleException => new RequestException(
                'cURL error 28: Operation timed out for ' . $uri,
                new GuzzleRequest('GET', $uri),
            ),
        ];

        $dataSets = [];
        foreach (self::clientDataProvider() as $clientLabel => [$clientClassName]) {
            foreach ($exceptions as $exceptionLabel => $exceptionFactory) {
                $dataSets[$clientLabel . ' ' . $exceptionLabel] = [$clientClassName, $exceptionFactory];
            }
        }

        return $dataSets;
    }

    #[Test]
    #[DataProvider('transportExceptionDataProvider')]
    public function getResultWithTransportExceptionWillReturnServiceError(
        string $clientClassName,
        \Closure $exceptionFactory,
    ): void {
        $subject = $this->createClient($clientClassName);
        $uri = $this->getRequestUri($clientClassName);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->with($uri)
            ->willThrowException($exceptionFactory($uri));

        $flashMessages = [];
        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->willReturnCallback(
                static function (string $message, string $title, ContextualFeedbackSeverity $severity) use (&$flashMessages): void {
                    $flashMessages[] = [$message, $title, $severity];
                },
            );

        $result = $subject->getResult($this->createGeocodeRequest($clientClassName));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError(ClientErrorEnum::SERVICE_ERROR, 'MapProvider could not be reached', 'Client Error'),
            $result->error,
        );
        self::assertSame(
            [['MapProvider could not be reached', 'Client Error', ContextualFeedbackSeverity::ERROR]],
            $flashMessages,
        );
    }

    #[Test]
    #[DataProvider('transportExceptionDataProvider')]
    public function getResultWithTransportExceptionWillNotExposeUriOrApiKey(
        string $clientClassName,
        \Closure $exceptionFactory,
    ): void {
        $subject = $this->createClient($clientClassName);
        $uri = $this->getRequestUri($clientClassName);
        $host = (string)parse_url($uri, PHP_URL_HOST);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willThrowException($exceptionFactory($uri));

        $flashMessageText = '';
        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->willReturnCallback(
                static function (string $message, string $title) use (&$flashMessageText): void {
                    $flashMessageText = $title . ' ' . $message;
                },
            );

        $error = $subject->getResult($this->createGeocodeRequest($clientClassName))->error;
        self::assertInstanceOf(ClientError::class, $error);

        foreach ([$flashMessageText, $error->title . ' ' . $error->message] as $text) {
            self::assertStringNotContainsString(self::API_KEY, $text);
            self::assertStringNotContainsString($host, $text);
            self::assertStringNotContainsString('Filderstadt', $text);
            self::assertStringNotContainsString('cURL', $text);
        }
    }

    #[Test]
    public function getResultWithInvalidRequestWillNotExposeUriOrApiKey(): void
    {
        $subject = $this->createClient(GoogleMapsClient::class);
        $request = $this->createGeocodeRequest(GoogleMapsClient::class);
        $request->setUri('https://maps.googleapis.com/maps/api/geocode/json?address=Filder stadt&key=' . self::API_KEY);

        $flashMessageText = '';
        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->willReturnCallback(
                static function (string $message, string $title) use (&$flashMessageText): void {
                    $flashMessageText = $title . ' ' . $message;
                },
            );

        $error = $subject->getResult($request)->error;
        self::assertInstanceOf(ClientError::class, $error);

        foreach ([$flashMessageText, $error->title . ' ' . $error->message] as $text) {
            self::assertStringNotContainsString(self::API_KEY, $text);
            self::assertStringNotContainsString('maps.googleapis.com', $text);
            self::assertStringNotContainsString('Filder', $text);
        }
    }

    #[Test]
    #[DataProvider('clientDataProvider')]
    public function getResultWithRequestExceptionWillNotExposeResponseBody(string $clientClassName): void
    {
        $subject = $this->createClient($clientClassName);
        $uri = $this->getRequestUri($clientClassName);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willThrowException(RequestException::create(
                new GuzzleRequest('GET', $uri),
                new GuzzleResponse(403, [], 'API key ' . self::API_KEY . ' is not allowed for your IP'),
            ));

        $flashMessageText = '';
        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->willReturnCallback(
                static function (string $message, string $title) use (&$flashMessageText): void {
                    $flashMessageText = $title . ' ' . $message;
                },
            );

        $error = $subject->getResult($this->createGeocodeRequest($clientClassName))->error;
        self::assertInstanceOf(ClientError::class, $error);

        foreach ([$flashMessageText, $error->title . ' ' . $error->message] as $text) {
            self::assertStringNotContainsString(self::API_KEY, $text);
            self::assertStringNotContainsString($uri, $text);
        }
    }

    #[Test]
    #[DataProvider('clientDataProvider')]
    public function getResultOfSameClientWillReturnIndependentResults(string $clientClassName): void
    {
        $subject = $this->createClient($clientClassName);
        $uri = $this->getRequestUri($clientClassName);

        $calls = 0;
        $this->requestFactoryMock
            ->expects(self::exactly(4))
            ->method('request')
            ->willReturnCallback(function () use (&$calls, $uri, $clientClassName) {
                return match (++$calls) {
                    1 => new HtmlResponse('Forbidden', 403),
                    2 => $this->createSuccessfulResponse($clientClassName),
                    3 => throw new ConnectException('Connection refused', new GuzzleRequest('GET', $uri)),
                    default => new HtmlResponse('Too many requests', 429),
                };
            });

        $request = $this->createGeocodeRequest($clientClassName);
        $firstResult = $subject->getResult($request);
        $secondResult = $subject->getResult($request);
        $thirdResult = $subject->getResult($request);
        $fourthResult = $subject->getResult($request);

        self::assertNotSame($firstResult, $secondResult);
        self::assertSame(ClientErrorEnum::REQUEST_DENIED, $firstResult->error?->type);
        self::assertFalse($secondResult->hasError());
        self::assertNotSame([], $secondResult->response);
        self::assertSame(ClientErrorEnum::SERVICE_ERROR, $thirdResult->error?->type);
        self::assertSame(ClientErrorEnum::QUOTA_EXCEEDED, $fourthResult->error?->type);

        // Previous results were not modified by the following requests
        self::assertSame(ClientErrorEnum::REQUEST_DENIED, $firstResult->error?->type);
        self::assertSame([], $firstResult->response);
    }

    #[Test]
    #[DataProvider('clientDataProvider')]
    public function getResultWillNotBeInfluencedByErrorsOfPreviousRequestsInFlashMessageQueue(
        string $clientClassName,
    ): void {
        $messageHelper = $this->get(MessageHelper::class);
        $messageHelper->getErrorMessages();

        $subject = $this->createClient($clientClassName, $messageHelper);

        $this->requestFactoryMock
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                new HtmlResponse('Forbidden', 403),
                $this->createSuccessfulResponse($clientClassName),
            );

        $request = $this->createGeocodeRequest($clientClassName);
        self::assertTrue($subject->getResult($request)->hasError());

        // The error of the first request is still in the queue (e.g. DataHandler processing multiple records)
        self::assertTrue($subject->hasErrors());

        $result = $subject->getResult($request);
        self::assertFalse($result->hasError());
        self::assertSame(
            json_decode((string)$this->createSuccessfulResponse($clientClassName)->getBody(), true),
            $result->response,
        );
    }

    #[Test]
    #[DataProvider('clientDataProvider')]
    public function getResultWillStillEnqueueFlashMessageForBackwardCompatibility(string $clientClassName): void
    {
        $messageHelper = $this->get(MessageHelper::class);
        // Start with an empty queue
        $messageHelper->getErrorMessages();

        $subject = $this->createClient($clientClassName, $messageHelper);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new HtmlResponse('Forbidden', 403));

        self::assertSame(
            [],
            $subject->processRequest($this->createGeocodeRequest($clientClassName)),
        );
        self::assertTrue($subject->hasErrors());

        $errors = $subject->getErrors();
        self::assertCount(1, $errors);
        self::assertSame(self::HTTP_ERROR_MESSAGE, $errors[0]->getMessage());
        self::assertSame('Client Error', $errors[0]->getTitle());
        self::assertSame(ContextualFeedbackSeverity::ERROR, $errors[0]->getSeverity());
        self::assertFalse($subject->hasErrors());
    }

    #[Test]
    public function getResultOfLegacyClientWithErrorInCheckResponseForErrorsWillReturnEmptyResponse(): void
    {
        $messageHelper = $this->get(MessageHelper::class);
        $messageHelper->getErrorMessages();

        $subject = $this->createClient(LegacyClient::class, $messageHelper);

        $this->requestFactoryMock
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'LEGACY_ERROR'], 200));

        $request = $this->createGeocodeRequest(LegacyClient::class);
        $result = $subject->getResult($request);

        // The legacy client can not provide a typed error
        self::assertSame([], $result->response);
        self::assertNull($result->error);
        self::assertSame([], $subject->processRequest($request));

        $errors = $subject->getErrors();
        self::assertCount(2, $errors);
        self::assertSame('Legacy client error', $errors[0]->getMessage());
        self::assertSame('Legacy', $errors[0]->getTitle());
    }

    #[Test]
    public function getResultOfLegacyClientWithoutErrorWillReturnResponse(): void
    {
        $messageHelper = $this->get(MessageHelper::class);
        $messageHelper->getErrorMessages();
        // Error of a previous request, which was not flushed
        $messageHelper->addFlashMessage('Previous error', 'Error', ContextualFeedbackSeverity::ERROR);

        $subject = $this->createClient(LegacyClient::class, $messageHelper);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse(['status' => 'OK'], 200));

        $result = $subject->getResult($this->createGeocodeRequest(LegacyClient::class));

        self::assertSame(['status' => 'OK'], $result->response);
        self::assertNull($result->error);
    }

    #[Test]
    public function getResultOfLegacyClientWithHttpErrorWillReturnTypedError(): void
    {
        $subject = $this->createClient(LegacyClient::class);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new HtmlResponse('Forbidden', 403));

        $this->expectHttpErrorFlashMessage();

        self::assertSame(
            ClientErrorEnum::REQUEST_DENIED,
            $subject->getResult($this->createGeocodeRequest(LegacyClient::class))->error?->type,
        );
    }

    #[Test]
    public function getResultOfLegacyClientWithInvalidJsonWillReturnEmptyResponse(): void
    {
        $subject = $this->createClient(LegacyClient::class);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new HtmlResponse('<html lang="en"></html>', 200));

        $result = $subject->getResult($this->createGeocodeRequest(LegacyClient::class));

        self::assertSame([], $result->response);
        self::assertNull($result->error);
    }

    /**
     * @param class-string<AbstractClient> $clientClassName
     */
    private function createClient(string $clientClassName, ?MessageHelper $messageHelper = null): AbstractClient
    {
        return new $clientClassName(
            $messageHelper ?? $this->messageHelperMock,
            $this->requestFactoryMock,
        );
    }

    private function createGeocodeRequest(string $clientClassName): AbstractRequest
    {
        $extConf = new ExtConf(
            defaultCountry: 'Germany',
            googleMapsGeocodeApiKey: self::API_KEY,
        );

        $request = $clientClassName === OpenStreetMapClient::class
            ? new OpenStreetMapGeocodeRequest($extConf)
            : new GoogleMapsGeocodeRequest($extConf);
        $request->addParameter('address', 'Filderstadt');

        return $request;
    }

    private function getRequestUri(string $clientClassName): string
    {
        return $this->createGeocodeRequest($clientClassName)->getUri();
    }

    private function createSuccessfulResponse(string $clientClassName): JsonResponse
    {
        if ($clientClassName === OpenStreetMapClient::class) {
            return new JsonResponse([['lat' => '48.6609', 'lon' => '9.2193']], 200);
        }

        return new JsonResponse(['status' => 'OK', 'results' => []], 200);
    }

    private function expectHttpErrorFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                self::HTTP_ERROR_MESSAGE,
                'Client Error',
                ContextualFeedbackSeverity::ERROR,
            );
    }
}
