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
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class SearchTest
 */
class SearchTest extends UnitTestCase
{
    protected Search $subject;

    protected function setUp(): void
    {
        $this->subject = new Search();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAddress(),
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
            $this->subject->getAddress(),
        );
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero(): void
    {
        self::assertSame(
            50,
            $this->subject->getRadius(),
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
            $this->subject->getRadius(),
        );
    }
}
