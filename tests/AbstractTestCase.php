<?php

declare(strict_types=1);

namespace Micoli\Elql\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AbstractTestCase extends TestCase
{
    protected string $databaseDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseDir = __DIR__ . '/tmp';
        $this->cleanupPath();
    }

    private function cleanupPath(): void
    {
        foreach (glob($this->databaseDir . '/*') as $file) {
            if (str_ends_with($file, '.gitignore')) {
                continue;
            }
            unlink($file);
        }
    }
}
