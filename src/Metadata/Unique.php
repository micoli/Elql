<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Unique
{
    public function __construct(
        public readonly string $expression,
        public readonly ?string $name = null,
    ) {
    }
}
