<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Persister;

use Micoli\Elql\Encoder\YamlEncoder;
use Micoli\Elql\Metadata\MetadataManager;
use Micoli\Elql\Persister\FilePersister;
use Micoli\Elql\Tests\AbstractTestCase;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FilePersisterTest extends AbstractTestCase
{
    private FilePersister $persister;

    protected function setUp(): void
    {
        parent::setUp();
        $this->persister = new FilePersister(
            $this->databaseDir,
            new MetadataManager(),
            YamlEncoder::FORMAT,
        );
    }

    public function testItShouldGetEmptydatabaseOnStartup(): void
    {
        self::assertCount(0, $this->persister->getDatabase());
    }
}
