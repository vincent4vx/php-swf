<?php

namespace Swf\Asset;

use RangeException;
use RuntimeException;
use Swf\Processor\Sprite\Rectangle;
use Swf\Processor\Sprite\SpriteInfoExtractor;
use Swf\Cli\Export\Export;

/**
 * A swf sprite
 */
final class Sprite implements AssetInterface
{
    /** List of available formats for a single frame */
    const FRAME_FORMATS = [
        Export::FORMAT_SVG,
        Export::FORMAT_PNG,
        Export::FORMAT_CANVAS,
        Export::FORMAT_BMP,
    ];

    /** List of available formats for animation file */
    const ANIMATION_FORMAT = [
        Export::FORMAT_GIF,
        Export::FORMAT_AVI,
    ];


    /**
     * @var string
     */
    private $directory;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var SpriteInfoExtractor|null
     */
    private $extractor;

    /**
     * @var Rectangle|null
     */
    private $bounds;

    /**
     * Sprite constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function type(): string
    {
        return Export::ITEM_TYPE_SPRITE;
    }

    /**
     * {@inheritdoc}
     */
    public function id(): int
    {
        return (int) explode('_', basename($this->directory), 3)[1];
    }

    /**
     * Get the sprite name, if provided
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return explode('_', basename($this->directory), 3)[2] ?? null;
    }

    /**
     * Get the file for the given frame number
     *
     * @param int $number The frame number. Starts at 1
     *
     * @return string
     */
    public function frame(int $number = 1): string
    {
        $file = $this->directory . DIRECTORY_SEPARATOR . $number . '.' . $this->frameFormat();

        if (!file_exists($file)) {
            throw new RangeException('Cannot found the frame number ' . $number);
        }

        return $file;
    }

    /**
     * Get the format for frames of the sprite
     *
     * @return string
     */
    public function frameFormat(): string
    {
        if ($this->format) {
            return $this->format;
        }

        foreach (self::FRAME_FORMATS as $format) {
            if (file_exists($this->directory . DIRECTORY_SEPARATOR . '1.' . $format)) {
                return $this->format = $format;
            }
        }

        throw new RuntimeException('Cannot detect the frame format');
    }

    /**
     * Get the frame format as mime type
     */
    public function mimeType(): string
    {
        if ($this->frameFormat() === Export::FORMAT_SVG) {
            return 'image/svg+xml';
        }

        return 'image/' . $this->frameFormat();
    }

    /**
     * @param SpriteInfoExtractor|null $extractor
     */
    public function setExtractor(?SpriteInfoExtractor $extractor): void
    {
        $this->extractor = $extractor;
    }

    /**
     * Get the bounds of the shape
     * Note: works only if an extractor is linked with the sprite
     *
     * @return Rectangle|null
     */
    public function bounds(): ?Rectangle
    {
        if ($this->bounds) {
            return $this->bounds;
        }

        return $this->bounds = $this->extractor->bounds($this->id());
    }
}
