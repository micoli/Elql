<?php

declare(strict_types=1);

namespace Micoli\Elql\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(public readonly ?string $name = null)
    {
    }
}
