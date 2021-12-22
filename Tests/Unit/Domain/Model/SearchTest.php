<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Domain\Model\Search;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class SearchTest
 */
class SearchTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var Search
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Search();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress(): void
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
    public function setAddressWithIntegerResultsInString(): void
    {
        $this->subject->setAddress(123);
        self::assertSame('123', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function setAddressWithBooleanResultsInString(): void
    {
        $this->subject->setAddress(true);
        self::assertSame('1', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero(): void
    {
        self::assertSame(
            50,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius(): void
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
    public function setRadiusWithStringResultsInInteger(): void
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
    public function setRadiusWithBooleanResultsInInteger(): void
    {
        $this->subject->setRadius(true);

        self::assertSame(
            1,
            $this->subject->getRadius()
        );
    }
}
