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
}
