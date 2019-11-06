<?php

namespace Swf\Processor;

use RuntimeException;
use Swf\Asset\AssetInterface;
use Swf\Cli\Export\Export;
use Swf\Cli\Export\ExportResult;
use Swf\Cli\Jar;
use Swf\SwfFile;

/**
 * Handle bulk extraction of assets
 * Extracts all assets in one command
 *
 * @todo configure assets types
 * @todo configure frame range
 */
final class BulkLoader
{
    /**
     * @var Jar
     */
    private $jar;

    /**
     * The assets loaders, indexed by there swf file
     *
     * @var AssetLoader[]
     */
    private $loaders;

    /**
     * List of assets to load
     *
     * @var string[]
     */
    private $toLoad = [];

    /**
     * Get the last export result
     *
     * @var ExportResult|null
     */
    private $result;


    /**
     * BulkLoader constructor.
     *
     * @param Jar $jar
     */
    public function __construct(Jar $jar)
    {
        $this->jar = $jar;
    }

    /**
     * Add a new source swf file
     *
     * @param SwfFile $file
     *
     * @return $this
     */
    public function addFile(SwfFile $file): self
    {
        $loader = $file->loader();

        if ($this->result) {
            $loader = $loader->withResult($this->result->file(basename($file->path())));
        }

        $this->loaders[$file->path()] = $loader;

        return $this;
    }

    /**
     * Change the result directory
     *
     * @param string $directory The directory path
     *
     * @return BulkLoader
     */
    public function setResultDirectory(string $directory): self
    {
        $this->result = new ExportResult($directory);

        foreach ($this->loaders as $file => $loader) {
            $this->loaders[$file] = $loader->withResult($this->result->file(basename($file)));
        }

        return $this;
    }

    /**
     * Add an asset to load
     *
     * @param string $name The asset's name
     *
     * @return $this
     */
    public function add(string $name): self
    {
        $this->toLoad[] = $name;

        return $this;
    }

    /**
     * Get an already loaded asset by its name
     *
     * @param string $name The asset's name
     *
     * @return AssetInterface|null
     */
    public function get(string $name): ?AssetInterface
    {
        foreach ($this->loaders as $swf => $loader) {
            if ($cached = $loader->findFromCache($name)) {
                return $cached;
            }
        }

        return null;
    }

    /**
     * Load all pending assets
     *
     * @return AssetInterface[]
     */
    public function load(): array
    {
        $loaded = [];
        $toLoad = [];

        foreach ($this->toLoad as $name) {
            foreach ($this->loaders as $swf => $loader) {
                if (!$loader->hasNamed($name)) {
                    continue;
                }

                if ($cached = $loader->findFromCache($name)) {
                    $loaded[$name] = $cached;
                } else {
                    $toLoad[$swf][$name] = $loader->resolveId($name);
                }
            }
        }

        if (empty($toLoad)) {
            return $loaded;
        }

        $this->toLoad = [];

        return $loaded + $this->loadFromSwf($toLoad);
    }

    /**
     * Try to extract assets from swf files
     *
     * @param array $toLoad
     *
     * @return array
     */
    private function loadFromSwf(array $toLoad): array
    {
        $export = new Export($this->jar);
        $export
            ->itemTypes([Export::ITEM_TYPE_ALL])
            ->input($input = $this->makeInputDirectory(array_keys($toLoad)))
        ;

        if ($this->result) {
            $export->output($this->result->path());
        }

        foreach ($toLoad as $ids) {
            $export->ids($ids);
        }

        $this->result = $export->execute();

        $loaded = [];

        foreach ($toLoad as $file => $ids) {
            $this->loaders[$file] = $loader = $this->loaders[$file]->withResult($this->result->file(basename($file)));

            foreach ($ids as $name => $id) {
                $loaded[$name] = $loader->getFromCache($id);
            }
        }

        return $loaded;
    }

    /**
     * Creates the temporary input directory for extract on multiple files
     *
     * @param string[] $files List of swf files to load
     *
     * @return string
     * @throws \Exception
     */
    private function makeInputDirectory(array $files)
    {
        // @todo utility class
        $tmp = sys_get_temp_dir();
        $attempts = 10;

        do {
            if ($attempts-- === 0) {
                throw new RuntimeException('Cannot create a temporary directory.');
            }

            $dir = $tmp . DIRECTORY_SEPARATOR . 'swf_input_' . bin2hex(random_bytes(8));
        } while (!mkdir($dir, 0700));

        foreach ($files as $file) {
            symlink($file, $dir . DIRECTORY_SEPARATOR . basename($file));
        }

        return $dir;
    }
}
