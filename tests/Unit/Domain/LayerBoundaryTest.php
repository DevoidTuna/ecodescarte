<?php

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * The architecture rule, enforced by a test rather than by convention.
 *
 * Without this, the first `use Illuminate\Support\Facades\...` inside the
 * domain slips past code review and the boundary dissolves quietly.
 */
class LayerBoundaryTest extends TestCase
{
    /**
     * The core must not know the framework, Eloquent, or the outer layers.
     *
     * @var list<string>
     */
    private const FORBIDDEN_PREFIXES = [
        'Illuminate\\',
        'Laravel\\',
        'App\\Models\\',
        'App\\Http\\',
        'App\\Infrastructure\\',
    ];

    public function test_the_domain_layer_does_not_depend_on_the_framework(): void
    {
        $this->assertSame([], $this->offendersIn($this->appPath('Domain')));
    }

    public function test_the_application_layer_does_not_depend_on_the_framework(): void
    {
        $this->assertSame([], $this->offendersIn($this->appPath('Application')));
    }

    public function test_the_check_is_not_vacuous(): void
    {
        // If the directories disappear or get renamed, the tests above would
        // pass without inspecting anything. This one rules out that false green.
        $this->assertGreaterThan(5, count($this->phpFilesIn($this->appPath('Domain'))));
        $this->assertGreaterThan(3, count($this->phpFilesIn($this->appPath('Application'))));
    }

    public function test_the_check_catches_a_forbidden_import(): void
    {
        // Proves the inspection really sees a violation, using a directory that
        // legitimately imports the framework: the adapter.
        $this->assertNotSame([], $this->offendersIn($this->appPath('Infrastructure/Persistence')));
    }

    private function appPath(string $relative): string
    {
        return dirname(__DIR__, 3).'/app/'.$relative;
    }

    /**
     * @return list<string>
     */
    private function offendersIn(string $directory): array
    {
        $offenders = [];

        foreach ($this->phpFilesIn($directory) as $path) {
            foreach (explode("\n", (string) file_get_contents($path)) as $line) {
                $line = trim($line);

                if (! str_starts_with($line, 'use ')) {
                    continue;
                }

                $imported = ltrim(substr($line, 4));

                foreach (self::FORBIDDEN_PREFIXES as $prefix) {
                    if (str_starts_with($imported, $prefix)) {
                        $offenders[] = basename($path).' imports '.rtrim($imported, ';');
                    }
                }
            }
        }

        sort($offenders);

        return $offenders;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
