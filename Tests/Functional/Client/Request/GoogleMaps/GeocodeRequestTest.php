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
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new GeocodeRequest(
            $this->extConf,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
        );

        parent::tearDown();
    }

    #[Test]
    public function setUriSetsUri(): void
    {
        $uri = 'https://www.jweiland.net';
        $this->subject->setUri($uri);
        self::assertSame(
            $uri,
            $this->subject->getUri(),
        );
    }

    #[Test]
    public function setParametersSetsParameters(): void
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $this->subject->setParameters($parameters);
        self::assertSame(
            $parameters,
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function addParameterSetsParameter(): void
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $this->subject->setParameters($parameters);
        $this->subject->addParameter('city', 'Filderstadt');
        self::assertSame(
            'Filderstadt',
            $this->subject->getParameter('city'),
        );
        self::assertCount(
            3,
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function hasParameterReturnsTrue(): void
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $this->subject->setParameters($parameters);
        self::assertTrue(
            $this->subject->hasParameter('uri'),
        );
    }

    #[Test]
    public function hasParameterReturnsFalse(): void
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57',
        ];
        $this->subject->setParameters($parameters);
        self::assertFalse(
            $this->subject->hasParameter('city'),
        );
    }

    #[Test]
    public function isValidRequestWithEmptyUriReturnsFalse(): void
    {
        $this->subject->setUri('  ');
        self::assertFalse(
            $this->subject->isValidRequest(),
        );
    }

    #[Test]
    public function isValidRequestWithInvalidUriReturnsFalse(): void
    {
        $this->subject->setUri('nice try');
        self::assertFalse(
            $this->subject->isValidRequest(),
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

    #[Test]
    public function getUriWillAddAddressAndApiKeyToUri(): void
    {
        $this->expectException(\Exception::class);

        $this->extConf->setGoogleMapsGeocodeApiKey('MyApiKey');
        $this->subject->setUri('%s:%s');
        $this->subject->addParameter('address', 'My Address');
        self::assertSame(
            'My%20Address:MyApiKey',
            $this->subject->getUri(),
        );
    }

    #[Test]
    public function getUriAddsAddressAndApiKeyToUriButUriIsInvalid(): void
    {
        $this->expectException(\Exception::class);

        $this->extConf->setGoogleMapsGeocodeApiKey('MyApiKey');
        $this->subject->setUri('%s:%s');
        $this->subject->addParameter('address', 'My Address');
        self::assertFalse(
            $this->subject->isValidRequest(),
        );
    }
}
