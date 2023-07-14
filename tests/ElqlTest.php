<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests;

use DateTimeImmutable;
use Micoli\Elql\Elql;
use Micoli\Elql\Encoder\YamlEncoder;
use Micoli\Elql\Exception\NonUniqueException;
use Micoli\Elql\Metadata\MetadataManager;
use Micoli\Elql\Persister\FilePersister;
use Micoli\Elql\Tests\Fixtures\Baz;
use Micoli\Elql\Tests\Fixtures\Foo;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ElqlTest extends AbstractTestCase
{
    private Elql $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new Elql(
            new FilePersister(
                $this->databaseDir,
                new MetadataManager(),
                YamlEncoder::FORMAT,
            ),
        );
    }

    public function testACompleteScenarioPass(): void
    {
        $this->database->add();
        $this->database->add(
            new Baz(1, 'a', 'a'),
            new Baz(2, 'b', 'b'),
            new Baz(3, 'c', 'c'),
            new Baz(4, 'd', 'd'),
            new Foo(1, 'aa', new DateTimeImmutable()),
        );
        $records = $this->database->find(Baz::class, 'record.id==3');
        self::assertCount(1, $records);
        self::assertSame('c', $records[0]->firstName);
        $this->database->update(Baz::class, function (Baz $record) {
            $record->firstName = $record->firstName . '-updated';

            return $record;
        }, 'record.id==3');

        $records = $this->database->find(Baz::class, 'record.id==3');
        self::assertSame('c-updated', $records[0]->firstName);

        $this->database->delete(Baz::class, 'record.id in [1,4]');
        $this->database->add(new Foo(2, 'bb', new DateTimeImmutable()));
        self::assertSame(2, $this->database->count(Baz::class));
        self::assertSame(2, $this->database->count(Foo::class));
        $this->database->persister->flush();
        self::assertSame(
            trim(
                <<<BAZ
                    -
                        id: 2
                        firstName: b
                        lastName: b
                    -
                        id: 3
                        firstName: c-updated
                        lastName: c
                    BAZ
            ),
            trim(file_get_contents($this->databaseDir . '/b_a_z.yaml')),
        );
        self::assertcount(2, $this->database->persister->getDatabase());
    }

    public function testItShouldReadFromFiles(): void
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
        self::assertSame(2, $this->database->count(Baz::class));
        $record = $this->database->find(Baz::class, 'record.id == 3')[0];
        self::assertSame(3, $record->id);
        self::assertSame('c', $record->firstName);
        self::assertSame('c', $record->lastName);
    }

    public function testItShouldNotAddIfUniqueIndexIsDuplicated(): void
    {
        $this->database->add(new Baz(1, 'a', 'a'));

        self::expectException(NonUniqueException::class);
        self::expectExceptionMessage('Duplicate on [record.id:1]');
        $this->database->add(new Baz(1, 'b', 'b'));
    }

    public function testItShouldNotAddIfMultipleUniqueIndexIsDuplicated(): void
    {
        $this->database->add(
            new Baz(1, 'a', 'a'),
            new Baz(2, 'b', 'b'),
        );

        self::expectException(NonUniqueException::class);
        self::expectExceptionMessage('Duplicate on [record.id:2], [fullname:["a","a"]]');
        $this->database->add(new Baz(2, 'a', 'a'));
    }

    public function testItUseExtendedExpressionLanguage(): void
    {
        $this->database->add(
            new Baz(1, 'a', 'a'),
            new Baz(2, 'b', 'b'),
        );

        self::assertCount(1, $this->database->find(Baz::class, 'strtoupper(record.firstName) === "A"'));
    }

    public function testItUseParametersInExpressionLanguage(): void
    {
        $this->database->add(
            new Baz(1, 'a', 'a'),
            new Baz(2, 'b', 'b'),
        );

        self::assertCount(1, $this->database->find(Baz::class, 'strtoupper(record.firstName) === value', ['value' => 'A']));
    }
}
