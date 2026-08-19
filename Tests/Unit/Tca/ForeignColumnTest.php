<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Tca;

use JWeiland\Maps2\Tca\ForeignColumn;
use JWeiland\Maps2\Tca\ForeignColumnResolveTypeEnum;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ForeignColumnTest
 */
class ForeignColumnTest extends UnitTestCase
{
    #[Test]
    public function createFromConfigurationWithStringReturnsSingleColumn(): void
    {
        $subject = ForeignColumn::createFromConfiguration('title');

        self::assertInstanceOf(ForeignColumn::class, $subject);
        self::assertSame(['title'], $subject->getColumnNames());
    }

    #[Test]
    public function createFromConfigurationWithEmptyStringReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration('   '));
    }

    #[Test]
    public function createFromConfigurationWithInvalidTypeReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration([
            'type' => 'foo',
            'columns' => ['title'],
        ]));
    }

    #[Test]
    public function createFromConfigurationWithSingleTypeReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration([
            'type' => 'single',
            'columns' => ['title'],
        ]));
    }

    #[Test]
    public function createFromConfigurationWithMissingColumnsReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
        ]));
    }

    #[Test]
    public function createFromConfigurationWithEmptyColumnsReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => ['', '   '],
        ]));
    }

    #[Test]
    public function createFromConfigurationWithNeitherStringNorArrayReturnsNull(): void
    {
        self::assertNull(ForeignColumn::createFromConfiguration(123));
    }

    #[Test]
    public function resolveValueWithSingleColumnReturnsTrimmedValue(): void
    {
        $subject = ForeignColumn::createFromConfiguration('title');

        self::assertSame(
            'jweiland.net',
            $subject->resolveValue(['title' => '  jweiland.net  ']),
        );
    }

    #[Test]
    public function resolveValueWithSingleColumnAndMissingKeyReturnsEmptyString(): void
    {
        $subject = ForeignColumn::createFromConfiguration('title');

        self::assertSame('', $subject->resolveValue([]));
    }

    #[Test]
    public function resolveValueWithCoalesceReturnsFirstNonEmptyColumn(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => ['company', 'name'],
        ]);

        self::assertSame(
            'ACME Inc.',
            $subject->resolveValue([
                'company' => 'ACME Inc.',
                'name' => 'Stefan',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithCoalesceSkipsEmptyAndBlankColumns(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => ['company', 'name'],
        ]);

        self::assertSame(
            'Stefan',
            $subject->resolveValue([
                'company' => '   ',
                'name' => 'Stefan',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithCoalesceAndAllColumnsEmptyReturnsEmptyString(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => ['company', 'name'],
        ]);

        self::assertSame('', $subject->resolveValue(['company' => '', 'name' => '']));
    }

    #[Test]
    public function resolveValueWithConcatJoinsColumnsWithDefaultGlue(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['first_name', 'last_name'],
        ]);

        self::assertSame(
            'Stefan Froemken',
            $subject->resolveValue([
                'first_name' => 'Stefan',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithConcatUsesConfiguredGlue(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['zip', 'city'],
            'glue' => ', ',
        ]);

        self::assertSame(
            '70794, Filderstadt',
            $subject->resolveValue([
                'zip' => '70794',
                'city' => 'Filderstadt',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithCommaGlueAndAllColumnsEmptyResolvesToEmptyStringWithoutStrayGlue(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['street', 'house_number', 'zip', 'city'],
            'glue' => ', ',
        ]);

        // Empty candidates are filtered out before imploding, so no leftover
        // ", , ," can appear, regardless of how many columns are empty.
        self::assertSame(
            '',
            $subject->resolveValue(['street' => '', 'house_number' => '', 'zip' => '', 'city' => '']),
        );
    }

    #[Test]
    public function resolveValueWithCommaGlueAndEmptyColumnsInTheMiddleSkipsThem(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['street', 'house_number', 'zip', 'city'],
            'glue' => ', ',
        ]);

        self::assertSame(
            'Echterdinger Str., Filderstadt',
            $subject->resolveValue([
                'street' => 'Echterdinger Str.',
                'house_number' => '',
                'zip' => '',
                'city' => 'Filderstadt',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithConcatSkipsEmptyColumns(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['first_name', 'last_name'],
        ]);

        self::assertSame(
            'Froemken',
            $subject->resolveValue([
                'first_name' => '',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithNestedCoalesceAndConcatCombinesBothStrategies(): void
    {
        // company -> name -> concat(first_name, last_name)
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => [
                'company',
                'name',
                [
                    'type' => 'concat',
                    'columns' => ['first_name', 'last_name'],
                    'glue' => ' ',
                ],
            ],
        ]);

        self::assertSame(
            'Stefan Froemken',
            $subject->resolveValue([
                'company' => '',
                'name' => '',
                'first_name' => 'Stefan',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithConcatOfWhitespaceOnlyColumnsResolvesToEmptyStringWithoutGlue(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => ['first_name', 'last_name'],
            'glue' => ' ',
        ]);

        // Blank (whitespace-only) columns must be trimmed to '' before the
        // emptiness check, so no glue leaks into the result.
        self::assertSame(
            '',
            $subject->resolveValue([
                'first_name' => '   ',
                'last_name' => "\t\n",
            ]),
        );
    }

    #[Test]
    public function resolveValueWithCoalesceSkipsConcatOfWhitespaceOnlyColumns(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => [
                [
                    'type' => 'concat',
                    'columns' => ['first_name', 'last_name'],
                    'glue' => ' ',
                ],
                'company',
            ],
        ]);

        self::assertSame(
            'ACME Inc.',
            $subject->resolveValue([
                'first_name' => '   ',
                'last_name' => '   ',
                'company' => 'ACME Inc.',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithConcatContainingNestedCoalesceCombinesBothStrategies(): void
    {
        // concat(first_name, coalesce(company, last_name))
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'concat',
            'columns' => [
                'first_name',
                [
                    'type' => 'coalesce',
                    'columns' => ['company', 'last_name'],
                ],
            ],
            'glue' => ' ',
        ]);

        self::assertSame(
            'Stefan ACME Inc.',
            $subject->resolveValue([
                'first_name' => 'Stefan',
                'company' => 'ACME Inc.',
                'last_name' => 'Froemken',
            ]),
        );

        self::assertSame(
            'Stefan Froemken',
            $subject->resolveValue([
                'first_name' => 'Stefan',
                'company' => '',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function resolveValueWithDeeplyMixedNestingFallsBackWhenInnerConcatResolvesEmpty(): void
    {
        // coalesce( concat( coalesce(a, b), c ), d )
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => [
                [
                    'type' => 'concat',
                    'columns' => [
                        [
                            'type' => 'coalesce',
                            'columns' => ['a', 'b'],
                        ],
                        'c',
                    ],
                    'glue' => '-',
                ],
                'd',
            ],
        ]);

        self::assertSame(
            ['a', 'b', 'c', 'd'],
            $subject->getColumnNames(),
        );

        self::assertSame(
            'A-C',
            $subject->resolveValue(['a' => 'A', 'b' => '', 'c' => 'C', 'd' => 'D']),
        );

        // Inner concat resolves to an empty string when a, b and c are all empty,
        // so the outer coalesce must fall back to the sibling column "d".
        self::assertSame(
            'D',
            $subject->resolveValue(['a' => '', 'b' => '', 'c' => '', 'd' => 'D']),
        );
    }

    #[Test]
    public function resolveValueWithNestedConfigurationPrefersOuterColumnsBeforeFallback(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => [
                'company',
                [
                    'type' => 'concat',
                    'columns' => ['first_name', 'last_name'],
                ],
            ],
        ]);

        self::assertSame(
            'ACME Inc.',
            $subject->resolveValue([
                'company' => 'ACME Inc.',
                'first_name' => 'Stefan',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function createFromConfigurationAcceptsResolveTypeEnumInstanceAsType(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => ForeignColumnResolveTypeEnum::CONCAT,
            'columns' => ['first_name', 'last_name'],
        ]);

        self::assertSame(
            'Stefan Froemken',
            $subject->resolveValue(['first_name' => 'Stefan', 'last_name' => 'Froemken']),
        );
    }

    #[Test]
    public function createFromConfigurationWithPlainListTreatsItAsImplicitCoalesce(): void
    {
        $subject = ForeignColumn::createFromConfiguration(['company', 'name']);

        self::assertSame(
            'jweiland.net',
            $subject->resolveValue(['company' => '', 'name' => 'jweiland.net']),
        );
    }

    #[Test]
    public function createFromConfigurationWithPlainListOfNestedConfigurationsFallsBackToNextEntry(): void
    {
        // Mirrors the real-world case: prefer company or name, otherwise fall back
        // to the concatenated first and last name.
        $subject = ForeignColumn::createFromConfiguration([
            [
                'type' => ForeignColumnResolveTypeEnum::COALESCE,
                'columns' => ['company', 'name'],
            ],
            [
                'type' => ForeignColumnResolveTypeEnum::CONCAT,
                'columns' => ['first_name', 'last_name'],
            ],
        ]);

        self::assertSame(
            'Stefan Froemken',
            $subject->resolveValue([
                'company' => '',
                'name' => '',
                'first_name' => 'Stefan',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function createFromConfigurationWithPlainListPrefersFirstNonEmptyGroup(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            [
                'type' => ForeignColumnResolveTypeEnum::COALESCE,
                'columns' => ['company', 'name'],
            ],
            [
                'type' => ForeignColumnResolveTypeEnum::CONCAT,
                'columns' => ['first_name', 'last_name'],
            ],
        ]);

        self::assertSame(
            'ACME Inc.',
            $subject->resolveValue([
                'company' => 'ACME Inc.',
                'name' => '',
                'first_name' => 'Stefan',
                'last_name' => 'Froemken',
            ]),
        );
    }

    #[Test]
    public function getColumnNamesReturnsFlatUniqueListForNestedConfiguration(): void
    {
        $subject = ForeignColumn::createFromConfiguration([
            'type' => 'coalesce',
            'columns' => [
                'company',
                'name',
                [
                    'type' => 'concat',
                    'columns' => ['first_name', 'last_name'],
                ],
            ],
        ]);

        self::assertSame(
            ['company', 'name', 'first_name', 'last_name'],
            $subject->getColumnNames(),
        );
    }
}
