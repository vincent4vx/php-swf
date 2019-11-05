<?php

namespace Swf\Cli\ToXml;

use SimpleXMLElement;
use Swf\Cli\Export\Export;

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
     * @var SimpleXMLElement
     */
    private $xml;

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
     * @return SimpleXMLElement[]
     */
    public function tagsByType(string $type): array
    {
        return $this->xml()->xpath('/swf/tags/item[@type="' . $type . 'Tag"]');
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

        foreach ($this->xml()->tags->item as $item) {
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
     * @return SimpleXMLElement
     */
    public function sprite(int $id): ?SimpleXMLElement
    {
        return $this->xml()->xpath('/swf/tags/item[@spriteId="' . $id . '"]')[0] ?? null;
    }

    /**
     * Get a shape definition
     *
     * @param int $id
     *
     * @return SimpleXMLElement
     */
    public function shape(int $id): ?SimpleXMLElement
    {
        return $this->xml()->xpath('/swf/tags/item[@shapeId="' . $id . '"]')[0] ?? null;
    }

    /**
     * Get the XML object
     *
     * @return SimpleXMLElement
     */
    public function xml(): SimpleXMLElement
    {
        if ($this->xml) {
            return $this->xml;
        }

        return $this->xml = simplexml_load_file($this->file);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['file'];
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
