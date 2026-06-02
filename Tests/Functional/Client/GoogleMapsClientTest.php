<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Google Maps Client class
 */
class GoogleMapsClientTest extends FunctionalTestCase
{
    protected GoogleMapsClient $subject;

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

        $language = new SiteLanguage(
            languageId: 1,
            locale: 'de_DE.utf8',
            base: new Uri('https://example.com/'),
            configuration: [
                'typo3Language' => 'en',
            ],
        );

        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('language', $language);

        $this->subject = new GoogleMapsClient(
            $this->messageHelperMock,
            $this->requestFactoryMock,
            new ExtConf(
                defaultCountry: 'Germany',
            ),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function processRequestWillAddFlashMessageBecauseOfClientError(): void
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            googleMapsGeocodeApiKey: 'ApiKey',
        ));

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=Filderstadt&key=ApiKey'
            )
            ->willReturn(new HtmlResponse('Client Error', 500));

        $this->messageHelperMock
            ->expects($this->atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'MapProvider returns a response with a status code different than 200',
                'Client Error',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest, 'Filderstadt'),
        );
    }

    #[Test]
    public function processRequestWillAddFlashMessageBecauseOfZeroResults(): void
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            googleMapsGeocodeApiKey: 'ApiKey',
        ));

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=Filderstadt&key=ApiKey'
            )
            ->willReturn(new JsonResponse(
                [
                    'status' => 'ZERO_RESULTS',
                ],
                200,
            ));

        $this->messageHelperMock
            ->expects($this->atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'Google Maps can\'t find any position. Please check your input and try again',
                'No positions found',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [
                'status' => 'ZERO_RESULTS',
            ],
            $this->subject->processRequest($geocodeRequest, 'Filderstadt'),
        );
    }

    #[Test]
    public function processRequestWillAddFlashMessageBecauseOfOtherErrors(): void
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            googleMapsGeocodeApiKey: 'ApiKey',
        ));

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=Filderstadt&key=ApiKey'
            )
            ->willReturn(new JsonResponse(
                [
                    'status' => 'INVALID_API_KEY',
                    'error_message' => 'Invalid API key',
                ],
                200,
            ));

        $this->messageHelperMock
            ->expects($this->atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'Invalid API key',
                'Error',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [
                'status' => 'INVALID_API_KEY',
                'error_message' => 'Invalid API key',
            ],
            $this->subject->processRequest($geocodeRequest, 'Filderstadt'),
        );
    }

    #[Test]
    public function processRequestWillRawUrlEncodeAddress(): void
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            googleMapsGeocodeApiKey: 'ApiKey',
        ));

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=K%C3%B6ln%20Bonn&key=ApiKey'
            )
            ->willReturn(new JsonResponse(
                [
                    'status' => 'OK',
                ],
                200,
            ));

        self::assertSame(
            [
                'status' => 'OK',
            ],
            $this->subject->processRequest($geocodeRequest, 'Köln Bonn'),
        );
    }

    #[Test]
    public function processRequestWillAddDefaultCountryForZipAddress(): void
    {
        $geocodeRequest = new GeocodeRequest(new ExtConf(
            googleMapsGeocodeApiKey: 'ApiKey',
        ));

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('request')
            ->with(
                'https://maps.googleapis.com/maps/api/geocode/json?address=12345%20Germany&key=ApiKey'
            )
            ->willReturn(new JsonResponse(
                [
                    'status' => 'OK',
                ],
                200,
            ));

        self::assertSame(
            [
                'status' => 'OK',
            ],
            $this->subject->processRequest($geocodeRequest, '12345'),
        );
    }
}
