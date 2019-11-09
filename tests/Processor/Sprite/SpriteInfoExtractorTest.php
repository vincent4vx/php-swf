<?php

namespace Swf\Processor\Sprite;

use PHPUnit\Framework\TestCase;
use Swf\Cli\Jar;
use Swf\SwfFile;
use Swf\SwfLoader;

/**
 * Class SpriteInfoExtractorTest
 */
class SpriteInfoExtractorTest extends TestCase
{
    /**
     * @var SpriteInfoExtractor
     */
    private $extractor;

    protected function setUp()
    {
        $this->extractor = new SpriteInfoExtractor(new SwfFile(new Jar(__DIR__.'/../../../bin/ffdec.jar'), __DIR__.'/../../_files/race3s.swf'));
    }

    /**
     *
     */
    public function test_dependencies()
    {
        $this->assertEquals([3], $this->extractor->dependencies(4));
        $this->assertEquals([6, 7, 8], $this->extractor->dependencies(9));
    }

    /**
     *
     */
    public function test_bounds()
    {
        $bounds = $this->extractor->bounds(4);
        $this->assertEquals(new Rectangle(-260, 12563, 681, 10744), $bounds);

        $this->assertEquals(-13, $bounds->Xoffset());
        $this->assertEquals(34, $bounds->Yoffset());
        $this->assertEquals(641, $bounds->width());
        $this->assertEquals(503, $bounds->height());

        $this->assertEquals(new Rectangle(-6411, 6412, -5031, 5032), $this->extractor->bounds(9));
    }

    /**
     *
     */
    public function test_bounds_with_matrix()
    {
        $this->extractor = new SpriteInfoExtractor((new SwfLoader())->load(__DIR__.'/../../_files/o3.swf'));
        $bounds = $this->extractor->bounds(120);

        $this->assertEquals(-29, $bounds->Xoffset());
        $this->assertEquals(-18, $bounds->Yoffset());
        $this->assertEquals(71, $bounds->width());
        $this->assertEquals(30, $bounds->height());
    }

    /**
     *
     */
    public function test_bounds_with_multiple_shapes()
    {
        $this->extractor = new SpriteInfoExtractor((new SwfLoader())->load(__DIR__.'/../../_files/o3.swf'));
        $bounds = $this->extractor->bounds(1254);

        $this->assertEquals(32, $bounds->width());
        $this->assertEquals(16, $bounds->height());
        $this->assertEquals(-15, $bounds->Xoffset());
        $this->assertEquals(-7, $bounds->Yoffset());
    }
}
