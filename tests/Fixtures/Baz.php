<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Fixtures;

use Micoli\Elql\Metadata\Table;
use Micoli\Elql\Metadata\Unique;

#[Table('b_a_z')]
#[Unique('record.id')]
#[Unique('[record.firstName,record.lastName]', 'fullname')]
class Baz
{
    public function __construct(
        public readonly int $id,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
