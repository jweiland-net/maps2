<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client\Request\OpenStreetMap;

use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test Open Street Map Geocode Request class
 */
class GeocodeRequestTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var GeocodeRequest
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf([]);
        $this->subject = new GeocodeRequest($this->extConf);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->extConf
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function setUriSetsUri()
    {
        $uri = 'https://www.jweiland.net';
        $this->subject->setUri($uri);
        self::assertSame(
            $uri,
            $this->subject->getUri()
        );
    }

    /**
     * @test
     */
    public function setParametersSetsParameters()
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57'
        ];
        $this->subject->setParameters($parameters);
        self::assertSame(
            $parameters,
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function addParameterSetsParameter()
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57'
        ];
        $this->subject->setParameters($parameters);
        $this->subject->addParameter('city', 'Filderstadt');
        self::assertSame(
            'Filderstadt',
            $this->subject->getParameter('city')
        );
        self::assertCount(
            3,
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function hasParameterReturnsTrue()
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57'
        ];
        $this->subject->setParameters($parameters);
        self::assertTrue(
            $this->subject->hasParameter('uri')
        );
    }

    /**
     * @test
     */
    public function hasParameterReturnsFalse()
    {
        $parameters = [
            'uri' => 'https://www.jweiland.net',
            'address' => 'Echterdinger Straße 57'
        ];
        $this->subject->setParameters($parameters);
        self::assertFalse(
            $this->subject->hasParameter('city')
        );
    }

    /**
     * @test
     */
    public function isValidRequestWithEmptyUriReturnsFalse()
    {
        $this->subject->setUri('  ');
        self::assertFalse(
            $this->subject->isValidRequest()
        );
    }

    /**
     * @test
     */
    public function isValidRequestWithInvalidUriReturnsFalse()
    {
        $this->subject->setUri('nice try');
        self::assertFalse(
            $this->subject->isValidRequest()
        );
    }

    /**
     * @test
     */
    public function isValidRequestWithValidUriReturnsTrue()
    {
        $this->subject->setUri('https://www.jweiland.net/what/ever/%s.html');
        self::assertTrue(
            $this->subject->isValidRequest()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getUriWillAddAddressAndApiKeyToUri()
    {
        $this->subject->setUri('https://www.jweiland.net/what/ever/%s.html');
        $this->subject->addParameter('address', 'My Address');
        self::assertSame(
            'https://www.jweiland.net/what/ever/My%20Address.html',
            $this->subject->getUri()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getUriAddsAddressAndApiKeyToUriButUriIsInvalid()
    {
        $this->subject->setUri('nice try');
        $this->subject->addParameter('address', 'My Address');
        self::assertFalse(
            $this->subject->isValidRequest()
        );
    }
}
