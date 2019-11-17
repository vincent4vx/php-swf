<?php

namespace Swf\Cli\ToXml;

use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Stream\Streams;
use Bdf\Collection\Util\OptionalInterface;
use SimpleXMLElement;
use Swf\Cli\Export\Export;
use XMLReader;

/**
 * Store the result of ToXml command
 *
 * @see ToXml
 */
final class SwfXml
{
    /**
     * @var string
     */
    private $file;

    /**
     * SwfXml constructor.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * Extract tags of the given type
     *
     * @param string $type The type name (without suffix "Tag")
     *
     * @return StreamInterface|SimpleXMLElement[]
     */
    public function tagsByType(string $type): StreamInterface
    {
        return $this->tags()->filter(function (SimpleXMLElement $e) use($type) { return $e['type'] == $type.'Tag'; });
    }

    /**
     * Get all exported assets, and return the asset name mapped with the character id
     *
     * @return integer[]
     */
    public function exportedAssets(): array
    {
        $exported = [];

        foreach ($this->tagsByType('ExportAssets') as $export) {
            $characterId = $export->tags->item;

            foreach ($export->names->item as $name) {
                $exported[(string) $name] = (int) $characterId;
            }
        }

        return $exported;
    }

    /**
     * Get the map of class name to sprite character id, defined by SymbolClassTag
     *
     * @return integer[]
     */
    public function symbolClass(): array
    {
        $symbols = [];

        foreach ($this->tagsByType('SymbolClass') as $symbol) {
            $index = 0;

            foreach ($symbol->tags->item as $characterId) {
                $name = (string) $symbol->names->item[$index++];
                $symbols[$name] = (int) $characterId;
            }
        }

        return $symbols;
    }

    /**
     * Get list of all assets types
     *
     * @return string[] The character id as key, and the type as value
     */
    public function assetTypes(): array
    {
        $assets = [];

        foreach ($this->read(['swf', 'tags', 'item']) as $item) {
            // @todo add other tags
            switch ((string) $item['type']) {
                case 'DefineShapeTag':
                case 'DefineShape2Tag':
                case 'DefineShape3Tag':
                    $assets[(int) $item['shapeId']] = Export::ITEM_TYPE_SHAPE;
                    break;

                case 'DefineSpriteTag':
                    $assets[(int) $item['spriteId']] = Export::ITEM_TYPE_SPRITE;
                    break;

                case 'DefineSoundTag':
                    $assets[(int) $item['soundId']] = Export::ITEM_TYPE_SOUND;
                    break;

                case 'DefineBitsJPEG3Tag':
                    $assets[(int) $item['characterID']] = Export::ITEM_TYPE_IMAGE;
                    break;
            }
        }

        return $assets;
    }

    /**
     * Get a sprite definition
     *
     * @param int $id
     *
     * @return SimpleXMLElement|OptionalInterface
     */
    public function sprite(int $id): OptionalInterface
    {
        return $this->tags()->filter(function ($item) use($id) { return $item['spriteId'] == $id; })->first();
    }

    /**
     * Get a shape definition
     *
     * @param int $id
     *
     * @return SimpleXMLElement|OptionalInterface
     */
    public function shape(int $id): OptionalInterface
    {
        return $this->tags()->filter(function ($item) use($id) { return $item['shapeId'] == $id; })->first();
    }

    /**
     * Open the Xml file
     *
     * @return XMLReader
     */
    public function open(): XMLReader
    {
        $reader = new XMLReader();
        $reader->open('file://'.$this->file);

        return $reader;
    }

    /**
     * Read XML elements from XMLReader
     *
     * Ex: `read(['swf', 'tags', 'item'])` For get all <item> elements into <swf> and <tags> elements
     *
     * @param string[] $path The elements paths. Must include the root path, and the requested element
     *
     * @return iterable|SimpleXMLElement[]
     */
    public function read(array $path): iterable
    {
        $reader = $this->open();
        $requestedDepth = count($path) - 1;

        try {
            if (!$reader->read()) {
                return;
            }

            do {
                if ($reader->name === $path[$reader->depth]) {
                    if ($reader->depth === $requestedDepth) {
                        yield new SimpleXMLElement($reader->readOuterXml());
                    } else {
                        if (!$reader->read()) {
                            return;
                        }
                    }
                }
            } while ($reader->next());
        } finally {
            $reader->close();
        }
    }

    /**
     * Get all Swf tag elements
     *
     * @return StreamInterface|SimpleXMLElement[]
     */
    public function tags(): StreamInterface
    {
        return Streams::wrap($this->read(['swf', 'tags', 'item']));
    }

    /**
     * Get the xml file path
     *
     * @return string
     */
    public function path(): string
    {
        return realpath($this->file);
    }

    /**
     * Remove the XML file
     */
    public function clear()
    {
        unlink($this->file);
    }

    /**
     * Check if the Xml file exists
     *
     * @return bool
     */
    public function valid(): bool
    {
        return file_exists($this->file);
    }
}
