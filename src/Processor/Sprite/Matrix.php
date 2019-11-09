<?php

namespace Swf\Processor\Sprite;

/**
 * The transformation matrix
 */
final class Matrix
{
    /**
     * @var bool
     */
    private $hasScale = false;

    /**
     * @var int
     */
    private $scaleX = 0;

    /**
     * @var int
     */
    private $scaleY = 0;

    /**
     * @var bool
     */
    private $hasRotate = false;

    /**
     * @var int
     */
    private $rotateSkew0 = 0;

    /**
     * @var int
     */
    private $rotateSkew1 = 0;

    /**
     * @var int
     */
    private $translateX = 0;

    /**
     * @var int
     */
    private $translateY = 0;

    /**
     * Add scaling
     *
     * @param int $x
     * @param int $y
     *
     * @return Matrix
     */
    public function scale(int $x, int $y): self
    {
        $this->hasScale = true;
        $this->scaleX = $x;
        $this->scaleY = $y;

        return $this;
    }

    /**
     * Add rotation
     *
     * @param int $rotateSkew0
     * @param int $rotateSkew1
     *
     * @return Matrix
     */
    public function rotate(int $rotateSkew0, int $rotateSkew1): self
    {
        $this->hasRotate = true;
        $this->rotateSkew0 = $rotateSkew0;
        $this->rotateSkew1 = $rotateSkew1;

        return $this;
    }

    /**
     * Add translation
     *
     * @param int $x
     * @param int $y
     *
     * @return Matrix
     */
    public function translate(int $x, int $y): self
    {
        $this->translateX = $x;
        $this->translateY = $y;

        return $this;
    }

    /**
     * Apply the matrix to the point
     *
     * @param Point $point
     *
     * @return Point
     */
    public function apply(Point $point): Point
    {
        $x = $point->x();
        $y = $point->y();

        if ($this->hasScale) {
            $x *= self::float($this->scaleX);
            $y *= self::float($this->scaleY);
        }

        if ($this->hasRotate) {
            $x += $point->y() * self::float($this->rotateSkew1);
            $y += $point->x() * self::float($this->rotateSkew0);
        }

        $x += $this->translateX;
        $y += $this->translateY;

        return new Point($x, $y);
    }

    /**
     * Convert into to float
     *
     * @param int $i
     *
     * @return float
     */
    static private function float(int $i): float
    {
        return $i / (1 << 16);
    }
}
