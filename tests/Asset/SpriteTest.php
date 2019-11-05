<?php


namespace Swf\Asset;

use PHPUnit\Framework\TestCase;
use Swf\Cli\Export\Export;

/**
 * Class SpriteTest
 */
class SpriteTest extends TestCase
{
    /**
     * @var Sprite
     */
    private $sprite;

    protected function setUp()
    {
        $this->sprite = new Sprite(__DIR__.'/../_files/sprites/DefineSprite_22_race3s_fla.readySet_7');
    }

    /**
     *
     */
    public function test_getters()
    {
        $this->assertSame(22, $this->sprite->id());
        $this->assertEquals('race3s_fla.readySet_7', $this->sprite->name());
        $this->assertEquals(Export::ITEM_TYPE_SPRITE, $this->sprite->type());
    }

    /**
     *
     */
    public function test_frameFormat()
    {
        $this->assertEquals(Export::FORMAT_SVG, $this->sprite->frameFormat());
        $this->assertEquals(Export::FORMAT_SVG, $this->sprite->frameFormat());
    }

    /**
     *
     */
    public function test_mimeType()
    {
        $this->assertEquals('image/svg+xml', $this->sprite->mimeType());
    }

    /**
     *
     */
    public function test_frame()
    {
        $this->assertEquals(__DIR__.'/../_files/sprites/DefineSprite_22_race3s_fla.readySet_7/1.svg', $this->sprite->frame());
        $this->assertEquals(__DIR__.'/../_files/sprites/DefineSprite_22_race3s_fla.readySet_7/15.svg', $this->sprite->frame(15));
    }

    /**
     *
     */
    public function test_frame_out_of_bounds()
    {
        $this->expectException(\RangeException::class);
        $this->expectExceptionMessage('Cannot found the frame number 404');

        $this->sprite->frame(404);
    }
}
