<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Domain\Model\Position;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class PositionTest
 */
class PositionTest extends UnitTestCase
{
    /**
     * @var Position
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Position();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude()
    {
        $this->subject->setLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude()
    {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function getFormattedAddressInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getFormattedAddress()
        );
    }

    /**
     * @test
     */
    public function setFormattedAddressSetsFormattedAddress()
    {
        $this->subject->setFormattedAddress('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFormattedAddress()
        );
    }

    /**
     * @test
     */
    public function setFormattedAddressWithIntegerResultsInString()
    {
        $this->subject->setFormattedAddress(123);
        self::assertSame('123', $this->subject->getFormattedAddress());
    }

    /**
     * @test
     */
    public function setFormattedAddressWithBooleanResultsInString()
    {
        $this->subject->setFormattedAddress(true);
        self::assertSame('1', $this->subject->getFormattedAddress());
    }
}
