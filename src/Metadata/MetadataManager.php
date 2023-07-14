<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

use ReflectionClass;

class MetadataManager implements MetadataManagerInterface
{
    /**
     * @var array<class-string, string>
     */
    private array $tableNames = [];

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
            if (!$attribute instanceof Table) {
                continue;
            }
            if ($attribute->name === null) {
                continue;
            }
            $this->tableNames[$model] = $attribute->name;

            return $this->tableNames[$model];
        }

        $parts = explode('\\', $model);

        $this->tableNames[$model] = $parts[count($parts) - 1];

        return $this->tableNames[$model];
    }
}
