<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Hook\Form;

use JWeiland\Maps2\Hook\Form\NormalizePoiPositionHook;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class NormalizePoiPositionHookTest extends UnitTestCase
{
    private NormalizePoiPositionHook $subject;

    private FormRuntime $formRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formRuntime = $this->createMock(FormRuntime::class);
        $this->subject = new NormalizePoiPositionHook();
    }

    #[Test]
    public function afterSubmitWithOtherElementTypeReturnsValueUntouched(): void
    {
        $value = ['latitude' => '', 'longitude' => ''];

        self::assertSame(
            $value,
            $this->subject->afterSubmit($this->formRuntime, $this->createElement('Text'), $value),
        );
    }

    #[Test]
    public function afterSubmitWithPageReturnsValueUntouched(): void
    {
        self::assertSame(
            'foo',
            $this->subject->afterSubmit($this->formRuntime, $this->createMock(Page::class), 'foo'),
        );
    }

    public static function submittedValueDataProvider(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'empty coordinates' => [['latitude' => '', 'longitude' => ''], null],
            'whitespace coordinates' => [['latitude' => ' ', 'longitude' => "\t"], null],
            'string is passed to validator' => ['48.1', '48.1'],
            'trimmed coordinates' => [
                ['latitude' => ' 48.123456 ', 'longitude' => '9.123456 '],
                ['latitude' => '48.123456', 'longitude' => '9.123456'],
            ],
            'additional keys are removed' => [
                ['latitude' => '48.1', 'longitude' => '9.2', 'title' => '<script>'],
                ['latitude' => '48.1', 'longitude' => '9.2'],
            ],
            'nested arrays are emptied' => [
                ['latitude' => ['48.1'], 'longitude' => '9.2'],
                ['latitude' => '', 'longitude' => '9.2'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('submittedValueDataProvider')]
    public function afterSubmitNormalizesValue(mixed $value, mixed $expected): void
    {
        self::assertSame(
            $expected,
            $this->subject->afterSubmit($this->formRuntime, $this->createElement('Maps2PoiPosition'), $value),
        );
    }

    private function createElement(string $type): FormElementInterface
    {
        $element = $this->createMock(FormElementInterface::class);
        $element->method('getType')->willReturn($type);

        return $element;
    }
}
