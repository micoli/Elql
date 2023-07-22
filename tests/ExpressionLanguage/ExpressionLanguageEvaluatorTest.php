<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\ExpressionLanguage;

use Micoli\Elql\Exception\ExpressionLanguageException;
use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluator;
use Micoli\Elql\Tests\AbstractTestCase;
use Micoli\Elql\Tests\Fixtures\Baz;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ExpressionLanguageEvaluatorTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function ItShouldEvaluateExpression(): void
    {
        $evaluator = new ExpressionLanguageEvaluator();
        $baz = new Baz(1, 'a', 'b');
        self::assertSame('Ac', $evaluator->evaluate('strtoupper(record.firstName) ~ test', $baz, ['test' => 'c']));
    }

    /**
     * @test
     */
    public function ItShouldGetCustomException(): void
    {
        $evaluator = new ExpressionLanguageEvaluator();
        $baz = new Baz(1, 'a', 'b');
        self::expectException(ExpressionLanguageException::class);
        $evaluator->evaluate('strtoupper(record.firstName) AAAAAA', $baz, ['test' => 'c']);
    }
}
