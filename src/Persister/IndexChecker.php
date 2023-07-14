<?php

declare(strict_types=1);

namespace Micoli\Elql\Persister;

use Micoli\Elql\Exception\NonUniqueException;
use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluatorInterface;
use Micoli\Elql\Metadata\MetadataManagerInterface;

class IndexChecker
{
    public function __construct()
    {
    }

    public function checkUniqueIndexes(
        MetadataManagerInterface $metadataManager,
        ExpressionLanguageEvaluatorInterface $expressionLanguageEvaluator,
        object $record,
        InMemoryTable $inMemoryTable,
    ): void {
        $errors = [];
        foreach ($metadataManager->uniques($record::class) as $indexName => $indexExpression) {
            /**
             * @var string $newRecordIndexValue
             */
            $newRecordIndexValue = $expressionLanguageEvaluator->evaluate($indexExpression, $record);
            /**
             * @var mixed $data
             */
            foreach ($inMemoryTable->data as $data) {
                /**
                 * @var string $existingRecordIndexValue
                 */
                $existingRecordIndexValue = $expressionLanguageEvaluator->evaluate($indexExpression, $data);
                if ($existingRecordIndexValue === $newRecordIndexValue) {
                    $errors[] = sprintf('[%s:%s]', $indexName, json_encode($existingRecordIndexValue));
                }
            }
        }
        if (count($errors) > 0) {
            throw new NonUniqueException(sprintf('Duplicate on %s', implode(', ', $errors)));
        }
    }
}
