<?php

declare(strict_types=1);

namespace Micoli\Elql\ExpressionLanguage;

interface ExpressionLanguageEvaluatorInterface
{
    public function evaluate(string $expression, mixed $record, array $parameters = []): mixed;
}
