<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Domain\Model\Position;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class PositionTest
 */
class PositionTest extends UnitTestCase
{
    protected Position $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Position();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function getFormattedAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFormattedAddress(),
        );
    }

    #[Test]
    public function setFormattedAddressSetsFormattedAddress(): void
    {
        $this->subject->setFormattedAddress('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFormattedAddress(),
        );
    }

    #[Test]
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude(),
        );
    }

    #[Test]
    public function setLatitudeSetsLatitude(): void
    {
        $this->subject->setLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLatitude(),
        );
    }

    #[Test]
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude(),
        );
    }

    #[Test]
    public function setLongitudeSetsLongitude(): void
    {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude(),
        );
    }
}
