<?php

namespace Swf\Cli\Export;

use Swf\Asset\Sprite;

/**
 * The export result for sprites
 */
final class Sprites
{
    const BASE_NAME = 'DefineSprite_';

    /**
     * @var string
     */
    private $directory;


    /**
     * Sprites constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Get a sprite by its character id
     *
     * @param int $id The character id
     *
     * @return Sprite|null
     */
    public function byId(int $id): ?Sprite
    {
        if (is_dir($this->directory . DIRECTORY_SEPARATOR . self::BASE_NAME . $id)) {
            return new Sprite($this->directory . DIRECTORY_SEPARATOR . self::BASE_NAME . $id);
        }

        $paths = glob($this->directory . DIRECTORY_SEPARATOR . self::BASE_NAME . $id . '_*');

        if (empty($paths)) {
            return null;
        }

        return new Sprite(current($paths));
    }

    /**
     * Get a sprite by its name or alias
     *
     * @param string $name The sprite name
     *
     * @return Sprite|null
     */
    public function byName(string $name): ?Sprite
    {
        $paths = glob($this->directory . DIRECTORY_SEPARATOR . self::BASE_NAME . '*' . '_' . $name);

        if (empty($paths)) {
            return null;
        }

        return new Sprite(current($paths));
    }
}
