<?php
namespace JWeiland\Maps2\Tests\Unit\Client\Request\GoogleMaps;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test Geocode Request class
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
        $this->extConf = new ExtConf();
        $this->subject = new GeocodeRequest($this->extConf);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function setAddressWillAddAddressToParameters()
    {
        $this->subject->addParameter('address', 'My Address');
        $this->assertSame(
            [
                'address' => 'My Address'
            ],
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getUriWillAddAddressAndApiKeyToUri()
    {
        $this->extConf->setGoogleMapsGeocodeApiKey('MyApiKey');
        $this->subject->setUri('%s:%s');
        $this->subject->addParameter('address', 'My Address');
        $this->assertSame(
            'My%20Address:MyApiKey',
            $this->subject->getUri()
        );
    }
}
