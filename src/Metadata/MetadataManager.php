<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

use ReflectionClass;

class MetadataManager implements MetadataManagerInterface
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $uniques = [];

    public function __construct(
        /**
         * @var array<class-string, string>
         */
        private array $tableNames = [],
    ) {
    }

    /**
     * @param class-string $model
     */
    public function tableNameExtractor(string $model): string
    {
        if (isset($this->tableNames[$model])) {
            return $this->tableNames[$model];
        }
        $reflectedClass = new ReflectionClass($model);
        $attributes = $reflectedClass->getAttributes();

        foreach ($attributes as $reflectedAttribute) {
            $attribute = $reflectedAttribute->newInstance();
            // @codeCoverageIgnoreStart
            if (!$attribute instanceof Table) {
                continue;
            }
            // @codeCoverageIgnoreStart

            // @codeCoverageIgnoreEnd
            if ($attribute->name === null) {
                continue;
            }
            // @codeCoverageIgnoreEnd
            $this->tableNames[$model] = $attribute->name;

            return $this->tableNames[$model];
        }

        $parts = explode('\\', $model);

        $this->tableNames[$model] = $parts[count($parts) - 1];

        return $this->tableNames[$model];
    }

    /**
     * @param class-string $model
     *
     * @return array<string, string>
     */
    public function uniques(string $model): array
    {
        if (isset($this->uniques[$model])) {
            return $this->uniques[$model];
        }
        $reflectedClass = new ReflectionClass($model);
        $attributes = $reflectedClass->getAttributes();
        $uniques = [];
        foreach ($attributes as $reflectedAttribute) {
            $attribute = $reflectedAttribute->newInstance();
            // @codeCoverageIgnoreStart
            if (!$attribute instanceof Unique) {
                continue;
            }
            // @codeCoverageIgnoreStart

            $uniques[$attribute->name ?? $attribute->expression] = $attribute->expression;
        }

        $this->uniques[$model] = $uniques;

        return $uniques;
    }
}
