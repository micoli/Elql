<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

interface MetadataManagerInterface
{
    /**
     * @param class-string $model
     */
    public function tableNameExtractor(string $model): string;
}
