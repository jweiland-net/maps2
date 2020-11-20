<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

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
    public function getAddressInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress()
    {
        $this->subject->setAddress('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressWithIntegerResultsInString()
    {
        $this->subject->setAddress(123);
        self::assertSame('123', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function setAddressWithBooleanResultsInString()
    {
        $this->subject->setAddress(true);
        self::assertSame('1', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero()
    {
        self::assertSame(
            50,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius()
    {
        $this->subject->setRadius(123456);

        self::assertSame(
            123456,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithStringResultsInInteger()
    {
        $this->subject->setRadius('123Test');

        self::assertSame(
            123,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithBooleanResultsInInteger()
    {
        $this->subject->setRadius(true);

        self::assertSame(
            1,
            $this->subject->getRadius()
        );
    }
}
