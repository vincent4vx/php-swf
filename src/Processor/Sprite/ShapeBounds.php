<?php

namespace Swf\Processor\Sprite;

/**
 * Bounds of a shape
 */
final class ShapeBounds
{
    const DIVIDER = 20;

    /**
     * @var int
     */
    private $Xmin;

    /**
     * @var int
     */
    private $Xmax;

    /**
     * @var int
     */
    private $Ymin;

    /**
     * @var int
     */
    private $Ymax;


    /**
     * ShapeBounds constructor.
     *
     * @param int $Xmin
     * @param int $Xmax
     * @param int $Ymin
     * @param int $Ymax
     */
    public function __construct(int $Xmin, int $Xmax, int $Ymin, int $Ymax)
    {
        $this->Xmin = $Xmin;
        $this->Xmax = $Xmax;
        $this->Ymin = $Ymin;
        $this->Ymax = $Ymax;
    }

    /**
     * Get the sprite width
     *
     * @return float
     */
    public function width(): float
    {
        return ($this->Xmax - $this->Xmin) / self::DIVIDER;
    }

    /**
     * Get the sprite height
     *
     * @return float
     */
    public function height(): float
    {
        return ($this->Ymax - $this->Ymin) / self::DIVIDER;
    }

    /**
     * Get the offset on the X axis
     *
     * @return float
     */
    public function Xoffset(): float
    {
        return $this->Xmin / self::DIVIDER;
    }

    /**
     * Get the offset on the Y axis
     *
     * @return float
     */
    public function Yoffset(): float
    {
        return $this->Ymin / self::DIVIDER;
    }
}
