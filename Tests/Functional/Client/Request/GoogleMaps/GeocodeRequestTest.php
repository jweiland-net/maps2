<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client\Request\GoogleMaps;

use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Google Maps Geocode Request class
 */
class GeocodeRequestTest extends FunctionalTestCase
{
    protected GeocodeRequest $subject;

    protected ExtConf $extConf;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function setUriSetsUri(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $uri = 'https://www.jweiland.net';
        $subject->setUri($uri);
        self::assertSame(
            $uri,
            $subject->getUri(),
        );
    }

    #[Test]
    public function setParametersSetsParameters(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $subject->setParameters($parameters);
        self::assertSame(
            $parameters,
            $subject->getParameters(),
        );
    }

    #[Test]
    public function addParameterSetsParameter(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $subject->setParameters($parameters);
        $subject->addParameter('city', 'Filderstadt');
        self::assertSame(
            'Filderstadt',
            $subject->getParameter('city'),
        );
        self::assertCount(
            3,
            $subject->getParameters(),
        );
    }

    #[Test]
    public function hasParameterReturnsTrue(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $subject->setParameters($parameters);
        self::assertTrue(
            $subject->hasParameter('uri'),
        );
    }

    #[Test]
    public function hasParameterReturnsFalse(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $subject->setParameters($parameters);
        self::assertFalse(
            $subject->hasParameter('city'),
        );
    }

    #[Test]
    public function isValidRequestWithEmptyUriReturnsFalse(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $subject->setUri('  ');
        self::assertFalse(
            $subject->isValidRequest(),
        );
    }

    #[Test]
    public function isValidRequestWithInvalidUriReturnsFalse(): void
    {
        $config = [];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $subject->setUri('nice try');
        self::assertFalse(
            $subject->isValidRequest(),
        );
    }

    #[Test]
    public function isValidRequestWithValidUriReturnsTrue(): void
    {
        $this->subject->setUri('https://www.jweiland.net/%s/what/ever/%s.html');
        self::assertTrue(
            $this->subject->isValidRequest(),
        );
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function getUriWillAddAddressAndApiKeyToUri(): void
    {
        $config = [
            'googleMapsGeocodeApiKey' => 'MyApiKey',
        ];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $subject->setUri('%s:%s');
        $subject->addParameter('address', 'My Address');

        self::assertSame(
            'My%20Address:MyApiKey',
            $subject->getUri(),
        );
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function getUriAddsAddressAndApiKeyToUriButUriIsInvalid(): void
    {
        $config = [
            'googleMapsGeocodeApiKey' => 'MyApiKey',
        ];
        $extConf = new ExtConf(...$config);
        $subject = new GeocodeRequest(
            $extConf,
        );

        $subject->setUri('%s:%s');
        $subject->addParameter('address', 'My Address');
        self::assertFalse(
            $subject->isValidRequest(),
        );
    }
}
