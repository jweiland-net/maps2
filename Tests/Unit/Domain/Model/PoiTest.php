<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Domain\Model\Poi;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class PoiTest
 */
class PoiTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var Poi
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Poi();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getCruserIdInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdSetsCruserId(): void
    {
        $this->subject->setCruserId(123456);

        self::assertSame(
            123456,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdWithStringResultsInInteger(): void
    {
        $this->subject->setCruserId('123Test');

        self::assertSame(
            123,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdWithBooleanResultsInInteger(): void
    {
        $this->subject->setCruserId(true);

        self::assertSame(
            1,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function getPosIndexInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexSetsPosIndex(): void
    {
        $this->subject->setPosIndex(123456);

        self::assertSame(
            123456,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexWithStringResultsInInteger(): void
    {
        $this->subject->setPosIndex('123Test');

        self::assertSame(
            123,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexWithBooleanResultsInInteger(): void
    {
        $this->subject->setPosIndex(true);

        self::assertSame(
            1,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude(): void
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
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude(): void
    {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude()
        );
    }
}
