<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test GeoCodeResult value object
 */
class GeoCodeResultTest extends UnitTestCase
{
    #[Test]
    public function constructorWithoutArgumentsWillCreateEmptyResultWithoutError(): void
    {
        $subject = new GeoCodeResult();

        self::assertCount(0, $subject->positions);
        self::assertNull($subject->error);
        self::assertFalse($subject->hasError());
        self::assertNull($subject->getFirstPosition());
    }

    #[Test]
    public function getFirstPositionWillReturnFirstPosition(): void
    {
        $firstPosition = new Position();
        $firstPosition->setFormattedAddress('First');
        $secondPosition = new Position();
        $secondPosition->setFormattedAddress('Second');

        $positions = new ObjectStorage();
        $positions->attach($firstPosition);
        $positions->attach($secondPosition);

        $subject = new GeoCodeResult($positions);

        self::assertSame($firstPosition, $subject->getFirstPosition());
        self::assertFalse($subject->hasError());
    }

    #[Test]
    public function getFirstPositionWillReturnFirstPositionEvenIfStorageWasIteratedBefore(): void
    {
        $firstPosition = new Position();
        $secondPosition = new Position();

        $positions = new ObjectStorage();
        $positions->attach($firstPosition);
        $positions->attach($secondPosition);
        $positions->rewind();
        $positions->next();

        self::assertSame($firstPosition, (new GeoCodeResult($positions))->getFirstPosition());
    }

    #[Test]
    public function hasErrorWithErrorWillReturnTrue(): void
    {
        $error = new ClientError(ClientErrorEnum::REQUEST_DENIED, 'Denied', 'Error');
        $subject = new GeoCodeResult(new ObjectStorage(), $error);

        self::assertTrue($subject->hasError());
        self::assertSame($error, $subject->error);
        self::assertNull($subject->getFirstPosition());
    }
}
