<?php

declare(strict_types=1);

namespace Micoli\Elql\Persister;

use Doctrine\Common\Annotations\AnnotationReader;
use Micoli\Elql\Encoder\YamlEncoder;
use Micoli\Elql\Exception\SerializerException;
use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluator;
use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluatorInterface;
use Micoli\Elql\Metadata\MetadataManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FilePersister implements PersisterInterface
{
    /**
     * @var array<class-string, InMemoryTable>
     */
    private array $records = [];
    private Filesystem $filesystem;

    public function __construct(
        private readonly string $dir,
        private readonly MetadataManagerInterface $metadataManager,
        private readonly string $format = JsonEncoder::FORMAT,
        private readonly IndexChecker $indexChecker = new IndexChecker(),
        private readonly Serializer $serializer = new Serializer([
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                new ClassMetadataFactory(
                    new AnnotationLoader(
                        new AnnotationReader(),
                    ),
                ),
                null,
                null,
                new PhpDocExtractor(),
            ),
        ], [
            new JsonEncoder(),
            new YamlEncoder(),
        ]),
        private readonly ExpressionLanguageEvaluatorInterface $expressionLanguageEvaluator = new ExpressionLanguageEvaluator(),
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @template T
     *
     * @param class-string<T> $model
     *
     * @return InMemoryTable<T>
     */
    public function getRecords(string $model): InMemoryTable
    {
        if (isset($this->records[$model])) {
            /** @var InMemoryTable<T> */
            return $this->records[$model];
        }

        $filename = $this->getFilename($model);
        if (!$this->filesystem->exists($filename)) {
            $this->records[$model] = new InMemoryTable($model, []);

            return $this->records[$model];
        }
        try {
            /** @var array $values */
            $values = $this->serializer->deserialize(
                file_get_contents($filename),
                $model . '[]',
                $this->format,
            );
        } catch (ExceptionInterface $exception) {
            throw new SerializerException($exception->getMessage(), previous: $exception);
        }
        $this->records[$model] = new InMemoryTable($model, $values);

        return $this->records[$model];
    }

    public function getDatabase(): array
    {
        return $this->records;
    }

    public function addRecord(object $record): void
    {
        $inMemoryTable = $this->getRecords($record::class);
        $this->indexChecker->checkUniqueIndexes(
            $this->metadataManager,
            $this->expressionLanguageEvaluator,
            $record,
            $inMemoryTable,
        );

        $inMemoryTable->data[] = $record;
    }

    /**
     * @template T
     *
     * @param class-string<T> $model
     * @param array<array-key, T> $values
     */
    public function updateRecords(string $model, array $values): void
    {
        $this->getRecords($model)->data = $values;
    }

    public function flush(): void
    {
        foreach ($this->records as $model => $values) {
            $this->filesystem->dumpFile(
                $this->getFilename($model),
                $this->serializer->serialize($values->data, $this->format),
            );
        }
    }

    /**
     * @param class-string $model
     */
    public function getFilename(string $model): string
    {
        return sprintf(
            '%s/%s.%s',
            $this->dir,
            $this->metadataManager->tableNameExtractor($model),
            $this->format,
        );
    }
}
