<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Fixtures;

class Baz
{
    public function __construct(
        public readonly int $id,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
