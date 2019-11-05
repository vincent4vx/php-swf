<?php

namespace Swf\Processor\Sprite;

use PHPUnit\Framework\TestCase;
use Swf\Cli\Jar;
use Swf\SwfFile;

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
        $this->assertEquals(new ShapeBounds(-260, 12563, 681, 10744), $bounds);

        $this->assertEquals(-13, $bounds->Xoffset());
        $this->assertEquals(34.05, $bounds->Yoffset());
        $this->assertEquals(641.15, $bounds->width());
        $this->assertEquals(503.15, $bounds->height());

        // @todo Nested
        //$this->assertEquals(new ShapeBounds(-6400, 6400, -5000, 5000), $this->extractor->bounds(9));
    }
}
