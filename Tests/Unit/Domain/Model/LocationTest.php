<?php
namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

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

use JWeiland\Maps2\Domain\Model\Location;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class LocationTest
 */
class LocationTest extends UnitTestCase
{
    /**
     * @var Location
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new Location();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getLatInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getLat()
        );
    }

    /**
     * @test
     */
    public function setLatSetsLat() {
        $this->subject->setLat(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getLat()
        );
    }

    /**
     * @test
     */
    public function getLngInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getLng()
        );
    }

    /**
     * @test
     */
    public function setLngSetsLng() {
        $this->subject->setLng(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getLng()
        );
    }
}
