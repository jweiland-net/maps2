<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Client;

use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Client\ClientResult;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test ClientResult value object
 */
class ClientResultTest extends UnitTestCase
{
    #[Test]
    public function constructorWithoutArgumentsWillCreateEmptyResultWithoutError(): void
    {
        $subject = new ClientResult();

        self::assertSame([], $subject->response);
        self::assertNull($subject->error);
        self::assertFalse($subject->hasError());
    }

    #[Test]
    public function hasErrorWithResponseOnlyWillReturnFalse(): void
    {
        $subject = new ClientResult(['status' => 'OK']);

        self::assertSame(['status' => 'OK'], $subject->response);
        self::assertFalse($subject->hasError());
    }

    #[Test]
    public function hasErrorWithErrorWillReturnTrue(): void
    {
        $error = new ClientError(ClientErrorEnum::QUOTA_EXCEEDED, 'Quota exceeded', 'Error');
        $subject = new ClientResult([], $error);

        self::assertTrue($subject->hasError());
        self::assertSame($error, $subject->error);
        self::assertSame(ClientErrorEnum::QUOTA_EXCEEDED, $subject->error->type);
        self::assertSame('Quota exceeded', $subject->error->message);
        self::assertSame('Error', $subject->error->title);
    }
}
