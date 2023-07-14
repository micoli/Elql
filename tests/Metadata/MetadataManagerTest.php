<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Metadata;

use Attribute;
use Micoli\Elql\Metadata\MetadataManager;
use Micoli\Elql\Tests\Fixtures\Bar;
use Micoli\Elql\Tests\Fixtures\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MetadataManagerTest extends TestCase
{
    public function testItShouldGetTableNameFromAttributes(): void
    {
        $metadataManager = new MetadataManager([Foo::class => 'aaa']);
        self::assertSame('Bar', $metadataManager->tableNameExtractor(Bar::class));
        self::assertSame('aaa', $metadataManager->tableNameExtractor(Foo::class));
        self::assertSame('FooBar', $metadataManager->tableNameExtractor(FooBar::class));
    }
}

#[Attribute]
class FooBar
{
}
