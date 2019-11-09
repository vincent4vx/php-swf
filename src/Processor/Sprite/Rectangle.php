<?php

namespace Swf\Processor\Sprite;

use Bdf\Collection\Util\Functor\Transformer\Getter;

/**
 * Represents a rectangle, with upper left and bottom right points
 * Used for sprites and shapes bounds
 */
final class Rectangle
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
     * @return int
     */
    public function width(): int
    {
        return ($this->Xmax - $this->Xmin) / self::DIVIDER;
    }

    /**
     * Get the sprite height
     *
     * @return int
     */
    public function height(): int
    {
        return ($this->Ymax - $this->Ymin) / self::DIVIDER;
    }

    /**
     * Get the offset on the X axis
     *
     * @return int
     */
    public function Xoffset(): int
    {
        return $this->Xmin / self::DIVIDER;
    }

    /**
     * Get the offset on the Y axis
     *
     * @return int
     */
    public function Yoffset(): int
    {
        return $this->Ymin / self::DIVIDER;
    }

    /**
     * Get all points of the rectangle
     *
     * @return Point[]
     */
    public function points(): array
    {
        return [
            new Point($this->Xmin, $this->Ymin),
            new Point($this->Xmax, $this->Ymin),
            new Point($this->Xmax, $this->Ymax),
            new Point($this->Xmin, $this->Ymax),
        ];
    }

    /**
     * Apply the transformation matrix to the rectangle
     *
     * @param Matrix $matrix
     *
     * @return Rectangle The transformed rectangle
     */
    public function transform(Matrix $matrix): Rectangle
    {
        $points = array_map([$matrix, 'apply'], $this->points());

        $xValues = array_map(new Getter('x'), $points);
        $yValues = array_map(new Getter('y'), $points);

        return new self(min($xValues), max($xValues), min($yValues), max($yValues));
    }

    /**
     * Merge two rectangle, and get the rectangle that contains the two rectangles
     *
     * @param Rectangle $bounds
     *
     * @return Rectangle The transformed rectangle
     */
    public function merge(Rectangle $bounds): Rectangle
    {
        return new self(
            min($this->Xmin, $bounds->Xmin),
            max($this->Xmax, $bounds->Xmax),
            min($this->Ymin, $bounds->Ymin),
            max($this->Ymax, $bounds->Ymax)
        );
    }
}
