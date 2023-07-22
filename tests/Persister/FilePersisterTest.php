<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests\Persister;

use Micoli\Elql\Encoder\YamlEncoder;
use Micoli\Elql\Exception\SerializerException;
use Micoli\Elql\Metadata\MetadataManager;
use Micoli\Elql\Persister\FilePersister;
use Micoli\Elql\Tests\AbstractTestCase;
use Micoli\Elql\Tests\Fixtures\Baz;

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
        self::assertCount(0, $this->persister->getRecords(Baz::class)->data);
    }

    public function testItShouldGetNonEmptydatabaseOnStartup(): void
    {
        file_put_contents(
            $this->databaseDir . '/b_a_z.yaml',
            <<<BAZ
                -
                    id: 2
                    firstName: b
                    lastName: b
                -
                    id: 3
                    firstName: c
                    lastName: c
                BAZ
        );
        self::assertCount(2, $this->persister->getRecords(Baz::class)->data);
    }

    public function testItShouldThrowCustomExceptionIfReadFailure(): void
    {
        file_put_contents(
            $this->databaseDir . '/b_a_z.yaml',
            <<<BAZ
                -
                    id: "2"
                    firstName: b
                    lastName: b
                -
                    id: 3
                    fairstName: c
                    lastName: c
                BAZ
        );
        self::expectException(SerializerException::class);
        self::assertCount(2, $this->persister->getRecords(Baz::class)->data);
    }
}
