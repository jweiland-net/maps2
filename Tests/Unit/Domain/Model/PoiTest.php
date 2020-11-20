<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Domain\Model\Poi;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class PoiTest
 */
class PoiTest extends UnitTestCase
{
    /**
     * @var Poi
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new Poi();
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
    public function getCruserIdInitiallyReturnsZero() {
        self::assertSame(
            0,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdSetsCruserId() {
        $this->subject->setCruserId(123456);

        self::assertSame(
            123456,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdWithStringResultsInInteger() {
        $this->subject->setCruserId('123Test');

        self::assertSame(
            123,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdWithBooleanResultsInInteger() {
        $this->subject->setCruserId(true);

        self::assertSame(
            1,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function getPosIndexInitiallyReturnsZero() {
        self::assertSame(
            0,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexSetsPosIndex() {
        $this->subject->setPosIndex(123456);

        self::assertSame(
            123456,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexWithStringResultsInInteger() {
        $this->subject->setPosIndex('123Test');

        self::assertSame(
            123,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function setPosIndexWithBooleanResultsInInteger() {
        $this->subject->setPosIndex(true);

        self::assertSame(
            1,
            $this->subject->getPosIndex()
        );
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero() {
        self::assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude() {
        $this->subject->setLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero() {
        self::assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude() {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude()
        );
    }
}
