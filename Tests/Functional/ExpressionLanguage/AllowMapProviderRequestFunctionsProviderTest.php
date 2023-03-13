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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

/**
 * Test AllowMapProviderRequestCondition
 */
class AllowMapProviderRequestFunctionsProviderTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var MapHelper|ObjectProphecy
     */
    protected $mapHelperProphecy;

    protected AllowMapProviderRequestFunctionsProvider $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapHelperProphecy = $this->prophesize(MapHelper::class);

        $this->subject = new AllowMapProviderRequestFunctionsProvider(
            $this->mapHelperProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapHelperProphecy
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
        $this->mapHelperProphecy
            ->isRequestToMapProviderAllowed()
            ->shouldBeCalled()
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
