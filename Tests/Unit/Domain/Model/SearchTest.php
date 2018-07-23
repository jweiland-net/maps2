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

use JWeiland\Maps2\Domain\Model\Search;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class SearchTest
 */
class SearchTest extends UnitTestCase
{
    /**
     * @var Search
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new Search();
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
    public function getAddressInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress() {
        $this->subject->setAddress('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressWithIntegerResultsInString() {
        $this->subject->setAddress(123);
        $this->assertSame('123', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function setAddressWithBooleanResultsInString() {
        $this->subject->setAddress(true);
        $this->assertSame('1', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero() {
        $this->assertSame(
            50,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius() {
        $this->subject->setRadius(123456);

        $this->assertSame(
            123456,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithStringResultsInInteger() {
        $this->subject->setRadius('123Test');

        $this->assertSame(
            123,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithBooleanResultsInInteger() {
        $this->subject->setRadius(true);

        $this->assertSame(
            1,
            $this->subject->getRadius()
        );
    }
}
