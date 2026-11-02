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
use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
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
 * Test Open Street Map class
 */
class OpenStreetMapClientTest extends FunctionalTestCase
{
    protected OpenStreetMapClient $subject;

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

        $this->subject = new OpenStreetMapClient(
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
                'https://nominatim.openstreetmap.org/search?q=Filderstadt&format=json&addressdetails=1',
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
    public function processRequestWillRawUrlEncodeAddress(): void
    {
        $response = [['lat' => '50.9', 'lon' => '7.0']];

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with(
                'https://nominatim.openstreetmap.org/search?q=K%C3%B6ln%20Bonn&format=json&addressdetails=1',
            )
            ->willReturn(new JsonResponse($response, 200));

        self::assertSame(
            $response,
            $this->subject->processRequest($this->createGeocodeRequest('Köln Bonn')),
        );
    }

    #[Test]
    public function processRequestWillAddDefaultCountryForZipAddress(): void
    {
        $response = [['lat' => '48.6', 'lon' => '9.2']];

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with(
                'https://nominatim.openstreetmap.org/search?q=12345%20Germany&format=json&addressdetails=1',
            )
            ->willReturn(new JsonResponse($response, 200));

        self::assertSame(
            $response,
            $this->subject->processRequest($this->createGeocodeRequest('12345')),
        );
    }

    #[Test]
    public function getResultWithResultsWillReturnResponseWithoutError(): void
    {
        $response = [
            [
                'lat' => '48.6609',
                'lon' => '9.2193',
                'display_name' => 'Filderstadt, Germany',
            ],
        ];

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse($response, 200));

        $this->messageHelperMock
            ->expects(self::never())
            ->method('addFlashMessage');

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame($response, $result->response);
        self::assertNull($result->error);
        self::assertFalse($result->hasError());
    }

    #[Test]
    public function getResultWithEmptyResultWillReturnNoPositionsFoundError(): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('request')
            ->willReturn(new JsonResponse([], 200));

        $this->messageHelperMock
            ->expects(self::once())
            ->method('addFlashMessage')
            ->with(
                'Open Street Map can\'t find any position. Please check your input and try again',
                'No positions found',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame([], $result->response);
        self::assertTrue($result->hasError());
        self::assertEquals(
            new ClientError(
                ClientErrorEnum::NO_POSITIONS_FOUND,
                'Open Street Map can\'t find any position. Please check your input and try again',
                'No positions found',
            ),
            $result->error,
        );
    }

    public static function invalidJsonDataProvider(): array
    {
        return [
            'HTML instead of JSON' => ['<html lang="en"><body>Error</body></html>'],
            'empty body' => [''],
            'broken JSON' => ['[{"lat": "48.6609"'],
            'JSON string instead of array' => ['"OK"'],
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
                'The response of Open Street Map was not a valid JSON response.',
                'Invalid JSON response',
                ContextualFeedbackSeverity::ERROR,
            );

        $result = $this->subject->getResult($this->createGeocodeRequest('Filderstadt'));

        self::assertSame([], $result->response);
        self::assertEquals(
            new ClientError(
                ClientErrorEnum::INVALID_RESPONSE,
                'The response of Open Street Map was not a valid JSON response.',
                'Invalid JSON response',
            ),
            $result->error,
        );
    }

    private function createGeocodeRequest(string $address): GeocodeRequest
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            defaultCountry: 'Germany',
        ));
        $geocodeRequest->addParameter('address', $address);

        return $geocodeRequest;
    }
}
