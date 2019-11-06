<?php

namespace Swf\Processor;

use Swf\Asset\AssetInterface;
use Swf\Cli\Export\Export;
use Swf\Cli\Export\ExportResult;
use Swf\Processor\Sprite\SpriteInfoExtractor;
use Swf\SwfFile;

/**
 * Loads the embedded assets of a Swf file
 */
final class AssetLoader
{
    /**
     * @var SwfFile
     */
    private $file;

    /**
     * @var int[]|null
     */
    private $exports;

    /**
     * @var int[]|null
     */
    private $symbols;

    /**
     * List of available assets
     * The key is the character id, and the value, the asset type
     *
     * @var string[]
     */
    private $assets;

    /**
     * The stored export result
     *
     * @var ExportResult|null
     */
    private $result;

    /**
     * @var AssetInterface[]
     */
    private $cache = [];


    /**
     * AssetLoader constructor.
     *
     * @param SwfFile $file
     */
    public function __construct(SwfFile $file)
    {
        $this->file = $file;
    }

    /**
     * Try to resolve the asset id from is name
     *
     * @param string $name The exported name, or symbol class
     *
     * @return int|null The resolved id, or null if not found
     */
    public function resolveId(string $name): ?int
    {
        if ($this->exports === null) {
            $this->exports = $this->file->toXml()->exportedAssets();
        }

        if ($this->symbols === null) {
            $this->symbols = $this->file->toXml()->symbolClass();
        }

        return $this->exports[$name] ?? $this->symbols[$name] ?? null;
    }

    /**
     * Check if the Swf has the given asset
     *
     * @param int $characterId The asset's character id
     *
     * @return bool
     */
    public function has(int $characterId): bool
    {
        return $this->typeOf($characterId) !== null;
    }

    /**
     * Get the asset's type
     *
     * @param int $characterId The asset's character id
     *
     * @return string The type
     */
    public function typeOf(int $characterId): ?string
    {
        if ($this->assets === null) {
            $this->assets = $this->file->toXml()->assetTypes();
        }

        return $this->assets[$characterId] ?? null;
    }

    /**
     * Check if the swf has the given asset name
     *
     * @param string $name The asset name
     *
     * @return bool
     */
    public function hasNamed(string $name): bool
    {
        return $this->resolveId($name) !== null;
    }

    /**
     * Try to retrieve an asset by its name from the cache
     * This call will not trigger an export call
     *
     * @param string $name The asset name
     *
     * @return AssetInterface|null The asset, or null if not found
     */
    public function findFromCache(string $name): ?AssetInterface
    {
        if ($this->result === null) {
            return null;
        }

        $id = $this->resolveId($name);

        if ($id === null) {
            return null;
        }

        return $this->getFromCache($id);
    }

    /**
     * Try to retrieve an asset by its character id from the cache
     * This call will not trigger an export call
     *
     * @param int $id The asset's character id
     *
     * @return AssetInterface|null
     */
    public function getFromCache(int $id): ?AssetInterface
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        if ($this->result === null || !$this->has($id)) {
            return null;
        }

        switch ($this->typeOf($id)) {
            case Export::ITEM_TYPE_SPRITE:
                $sprite = $this->result->sprites()->byId($id);

                if ($sprite) {
                    $sprite->setExtractor(new SpriteInfoExtractor($this->file));
                }

                return $this->cache[$id] = $sprite;

            default:
                // @todo implements other assets, and throw exception here
                return null;
        }
    }

    /**
     * Extract the asset from the swf by its character id
     *
     * @param int $id The asset's character id
     *
     * @return AssetInterface|null
     */
    public function get(int $id): ?AssetInterface
    {
        if (!$this->has($id)) {
            return null;
        }

        if ($cached = $this->getFromCache($id)) {
            return $cached;
        }

        $export = $this->file->export();

        if ($this->result) {
            $export->output($this->result->path());
        }

        // @todo handle item types
        $this->result = $export->itemTypes([Export::ITEM_TYPE_ALL])->ids($id)->execute();

        return $this->getFromCache($id);
    }

    /**
     * Extract the asset from the swf by its name
     *
     * @param string $name The asset's name
     *
     * @return AssetInterface|null
     */
    public function find(string $name): ?AssetInterface
    {
        $id = $this->resolveId($name);

        if ($id === null) {
            return null;
        }

        return $this->get($id);
    }

    /**
     * Change the export result
     *
     * @param ExportResult $result
     *
     * @return self The new loader instance
     */
    public function withResult(ExportResult $result): self
    {
        if ($this->result && $this->result->path() === $result->path()) {
            return $this;
        }

        $loader = clone $this;

        $loader->result = $result;
        $loader->cache = [];

        return $loader;
    }
}
