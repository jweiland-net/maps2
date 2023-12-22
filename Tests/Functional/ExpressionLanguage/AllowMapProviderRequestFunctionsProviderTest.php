<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\ExpressionLanguage;

use JWeiland\Maps2\ExpressionLanguage\AllowMapProviderRequestFunctionsProvider;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test AllowMapProviderRequestCondition
 */
class AllowMapProviderRequestFunctionsProviderTest extends FunctionalTestCase
{
    /**
     * @var MapHelper|MockObject
     */
    protected $mapHelperMock;

    protected AllowMapProviderRequestFunctionsProvider $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapHelperMock = $this->createMock(MapHelper::class);

        $this->subject = new AllowMapProviderRequestFunctionsProvider($this->mapHelperMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapHelperMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getFunctionsWillReturnArrayWithExpressionFunction(): void
    {
        $expressionFunctions = $this->subject->getFunctions();

        foreach ($expressionFunctions as $expressionFunction) {
            self::assertInstanceOf(ExpressionFunction::class, $expressionFunction);
        }
    }

    /**
     * @test
     */
    public function getFunctionsWillReturnSpecificExpressionFunction(): void
    {
        $this->mapHelperMock
            ->expects(self::atLeastOnce())
            ->method('isRequestToMapProviderAllowed')
            ->willReturn(true);

        $expressionFunction = $this->subject->getFunctions()[0];

        self::assertSame(
            'isRequestToMapProviderAllowed',
            $expressionFunction->getName()
        );

        self::assertNull(
            call_user_func($expressionFunction->getCompiler())
        );

        self::assertTrue(
            call_user_func($expressionFunction->getEvaluator(), ['foo' => 'bar'])
        );
    }
}
