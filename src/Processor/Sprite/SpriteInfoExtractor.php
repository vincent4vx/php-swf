<?php

namespace Swf\Processor\Sprite;

use Bdf\Collection\Stream\Streams;
use SimpleXMLElement;
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
     * @return Rectangle|null
     */
    public function bounds(int $spriteId): ?Rectangle
    {
        return Streams::wrap($this->placeObjectMatrices($spriteId))
            ->mapKey(function (array $matrices, $characterId) {
                return $this->assetBounds($characterId);
            })
            ->filter(function (array $matrices, $key) {
                return $key !== null;
            })
            ->flatMap(function (array $matrices, Rectangle $bounds) {
                return Streams::wrap($matrices)->map([$bounds, 'transform']);
            })
            ->reduce(function (?Rectangle $a, Rectangle $b) {
                return $a ? $a->merge($b) : $b;
            })
        ;
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
        return $this->file->toXml()->sprite($spriteId)->subTags->item->map(function ($items) {
            $shapes = [];

            foreach ($items as $item) {
                if (!empty($item['characterId'])) {
                    $shapes[] = (int) $item['characterId'];
                }
            }

            return $shapes;
        })->or([]);
    }

    /**
     * Extract place objects matrices
     *
     * @param int $spriteId The sprite character id
     *
     * @return Matrix[][]
     */
    public function placeObjectMatrices(int $spriteId): array
    {
        return $this->file->toXml()->sprite($spriteId)->subTags->item->map(function ($items) {
            $matrices = [];

            foreach ($items as $item) {
                if (empty($item['characterId']) || substr($item['type'], 0, strlen('PlaceObject')) !== 'PlaceObject') {
                    continue;
                }

                $matrices[(int) $item['characterId']][] = $this->parseMatrixElement($item->matrix);
            }

            return $matrices;
        })->or([]); // @todo exception ?
    }

    /**
     * Creates the Matrix from the xml element
     *
     * @param SimpleXMLElement $element
     *
     * @return Matrix
     */
    private function parseMatrixElement(SimpleXMLElement $element): Matrix
    {
        $matrix = new Matrix();

        $matrix->translate((int) $element['translateX'], (int) $element['translateY']);

        if ($element['hasScale'] == 'true') {
            $matrix->scale((int) $element['scaleX'], (int) $element['scaleY']);
        }

        if ($element['hasRotate'] == 'true') {
            $matrix->rotate((int) $element['rotateSkew0'], (int) $element['rotateSkew1']);
        }

        return $matrix;
    }

    /**
     * Try to get bounds of an asset
     *
     * @param int $characterId
     *
     * @return Rectangle|null
     */
    private function assetBounds(int $characterId): ?Rectangle
    {
        return $this->file->toXml()->shape($characterId)
            ->map(function (SimpleXMLElement $shape) {
                // A shape is found
                return new Rectangle(
                    (int) $shape->shapeBounds['Xmin'],
                    (int) $shape->shapeBounds['Xmax'],
                    (int) $shape->shapeBounds['Ymin'],
                    (int) $shape->shapeBounds['Ymax']
                );
            })
            ->orSupply(function () use($characterId) {
                // Check for nested sprite
                return $this->bounds($characterId);
            })
        ;
    }
}
