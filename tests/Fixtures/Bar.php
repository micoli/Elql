<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Fixtures;

use Micoli\Elql\Metadata\Table;

#[Table]
class Bar
{
    public function __construct(
        public readonly int $id,
        public string $name,
    ) {
    }
}
