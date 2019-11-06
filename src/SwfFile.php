<?php

namespace Swf;

use ArrayAccess;
use BadMethodCallException;
use Swf\Asset\AssetInterface;
use Swf\Cli\Export\Export;
use Swf\Cli\Jar;
use Swf\Cli\ToXml\SwfXml;
use Swf\Cli\ToXml\ToXml;
use Swf\Processor\AssetLoader;

/**
 * Facade class for handle Swf files
 *
 * @see SwfLoader::load() For loading a SwfFile
 */
final class SwfFile implements ArrayAccess
{
    /**
     * @var Jar
     */
    private $jar;

    /**
     * @var string
     */
    private $file;

    /**
     * @var SwfXml|null
     */
    private $xml;

    /**
     * @var AssetLoader
     */
    private $assetLoader;


    /**
     * SwfFile constructor.
     *
     * @param Jar $jar
     * @param string $file
     */
    public function __construct(Jar $jar, string $file)
    {
        $this->jar = $jar;
        $this->file = $file;

        $this->assetLoader = new AssetLoader($this);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->assetLoader->hasNamed($offset) || $this->assetLoader->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?AssetInterface
    {
        return $this->assetLoader->find($offset) ?: $this->assetLoader->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Cannot modify the swf assets');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Cannot modify the swf assets');
    }

    /**
     * Get the asset loader
     * Note: Array access can also be used for accessing to assets
     *
     * @return AssetLoader
     */
    public function loader(): AssetLoader
    {
        return $this->assetLoader;
    }

    /**
     * Get the export command
     *
     * @return Export
     */
    public function export(): Export
    {
        return (new Export($this->jar))->input($this->file);
    }

    /**
     * Convert the Swf file to and Xml file
     *
     * @param string|null $filename
     *
     * @return SwfXml
     */
    public function toXml(?string $filename = null): SwfXml
    {
        if ($this->xml && $this->xml->valid() && (!$filename || realpath($filename) === $this->xml->path())) {
            return $this->xml;
        }

        return $this->xml = (new ToXml($this->jar))->input($this->file)->output($filename)->execute();
    }

    /**
     * Get the swf file path
     *
     * @return string
     */
    public function path(): string
    {
        return $this->file;
    }
}
