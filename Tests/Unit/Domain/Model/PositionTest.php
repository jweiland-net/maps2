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

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new Position();
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
    public function getNortheastInitiallyReturnsNull() {
        $this->assertNull($this->subject->getNortheast());
    }

    /**
     * @test
     */
    public function setNortheastSetsNortheast() {
        $instance = new Location();
        $this->subject->setNortheast($instance);

        $this->assertSame(
            $instance,
            $this->subject->getNortheast()
        );
    }

    /**
     * @test
     */
    public function getSouthwestInitiallyReturnsNull() {
        $this->assertNull($this->subject->getSouthwest());
    }

    /**
     * @test
     */
    public function setSouthwestSetsSouthwest() {
        $instance = new Location();
        $this->subject->setSouthwest($instance);

        $this->assertSame(
            $instance,
            $this->subject->getSouthwest()
        );
    }
}
