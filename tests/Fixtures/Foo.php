<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Fixtures;

use DateTimeImmutable;

class Foo
{
    public function __construct(
        public readonly int $id,
        public string $companyName,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
