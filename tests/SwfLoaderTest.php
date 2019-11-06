<?php


namespace Swf;

use PHPUnit\Framework\TestCase;
use Swf\Asset\Sprite;

/**
 * Class SwfLoaderTest
 */
class SwfLoaderTest extends TestCase
{
    /**
     * @var SwfLoader
     */
    private $loader;

    protected function setUp()
    {
        $this->loader = new SwfLoader();
    }

    /**
     *
     */
    public function test_load()
    {
        $swf = $this->loader->load(__DIR__.'/_files/race3s.swf');

        $this->assertInstanceOf(Sprite::class, $swf[4]);
    }

    /**
     *
     */
    public function test_bulk()
    {
        $bulk = $this->loader->bulk(glob(__DIR__.'/_files/*.swf'));

        $sprites = $bulk
            ->add('race3s_fla.readySet_7')
            ->add('WaterCircle')
            ->load()
        ;

        $this->assertContainsOnlyInstancesOf(Sprite::class, $sprites);
        $this->assertCount(2, $sprites);
        $this->assertArrayHasKey('race3s_fla.readySet_7', $sprites);
        $this->assertArrayHasKey('WaterCircle', $sprites);
    }
}
