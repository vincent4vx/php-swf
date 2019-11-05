<?php

namespace Swf\Processor\Sprite;

use Swf\SwfFile;

/**
 * Extract sprites metadata from swf
 */
final class SpriteInfoExtractor
{
    /**
     * @var SwfFile
     */
    private $file;


    /**
     * SpriteInfoExtractor constructor.
     *
     * @param SwfFile $file
     */
    public function __construct(SwfFile $file)
    {
        $this->file = $file;
    }

    /**
     * Get the sprite bounds
     *
     * @param int $spriteId The sprite character id
     *
     * @return ShapeBounds
     */
    public function bounds(int $spriteId): ShapeBounds
    {
        $shapes = $this->dependencies($spriteId);

        if (empty($shapes)) {
            return new ShapeBounds(0, 0, 0, 0); // @todo ?
        }

        // @todo handle multiple shapes
        // @todo handle nested sprites
        if (!($shape = $this->file->toXml()->shape($shapes[0]))) {
            return new ShapeBounds(0, 0, 0, 0); // @todo ?
        }

        $bounds = $shape->shapeBounds;

        return new ShapeBounds(
            (int) $bounds['Xmin'],
            (int) $bounds['Xmax'],
            (int) $bounds['Ymin'],
            (int) $bounds['Ymax']
        );
    }

    /**
     * Extract dependencies assets characters ids
     *
     * @param int $spriteId The sprite character id
     *
     * @return int[]
     */
    public function dependencies(int $spriteId): array
    {
        $shapes = [];

        foreach ($this->file->toXml()->sprite($spriteId)->subTags->item as $item) {
            if (!empty($item['characterId'])) {
                $shapes[] = (int) $item['characterId'];
            }
        }

        return $shapes;
    }
}
