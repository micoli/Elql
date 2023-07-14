<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

class MetadataManager implements MetadataManagerInterface
{
    /**
     * @param class-string $model
     */
    public function tableNameExtractor(string $model): string
    {
        $parts = explode('\\', $model);

        return $parts[count($parts) - 1];
    }
}
