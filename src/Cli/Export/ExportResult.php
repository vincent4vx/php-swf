<?php


namespace Swf\Cli\Export;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Results of the export command
 *
 * @see Export
 */
final class ExportResult
{
    /**
     * @var string
     */
    private $directory;


    /**
     * ExportResult constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Get the exported sprites
     *
     * @return Sprites
     */
    public function sprites(): Sprites
    {
        if (is_dir($this->directory . DIRECTORY_SEPARATOR . 'sprites')) {
            return new Sprites($this->directory . DIRECTORY_SEPARATOR . 'sprites');
        }

        return new Sprites($this->directory);
    }

    /**
     * Get a new export result for the given swf file
     *
     * @param string $name The name of the swf file
     *
     * @return ExportResult
     */
    public function file(string $name): ExportResult
    {
        return new self($this->directory . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * Get the path where the result is stored
     *
     * @return string
     */
    public function path(): string
    {
        return $this->directory;
    }

    /**
     * Clear all exported files
     *
     * Note: after call this method, the current object will be empty
     */
    public function clear(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        rmdir($this->directory);
    }
}
