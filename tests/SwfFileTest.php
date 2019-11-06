<?php

namespace Swf;

use PHPUnit\Framework\TestCase;
use Swf\Asset\Sprite;
use Swf\Cli\Export\Export;
use Swf\Cli\Jar;
use Swf\Processor\AssetLoader;

/**
 * Class SwfFileTest
 */
class SwfFileTest extends TestCase
{
    /**
     * @var SwfFile
     */
    private $file;

    /**
     *
     */
    protected function setUp()
    {
        $this->file = new SwfFile(new Jar(__DIR__.'/../bin/ffdec.jar'), __DIR__.'/_files/race3s.swf');
    }

    /**
     *
     */
    public function test_offsetIsset()
    {
        $this->assertTrue(isset($this->file[1]));
        $this->assertTrue(isset($this->file[4]));
        $this->assertFalse(isset($this->file[404]));
        $this->assertTrue(isset($this->file['race3s_fla.readySet_7']));
        $this->assertTrue(isset($this->file['EngineStart']));
    }

    /**
     *
     */
    public function test_offsetGet()
    {
        $this->assertInstanceOf(Sprite::class, $this->file[4]);
        $this->assertEquals(4, $this->file[4]->id());

        $this->assertEquals($this->file['race3s_fla.readySet_7'], $this->file[22]);
    }

    /**
     *
     */
    public function test_loader()
    {
        $this->assertInstanceOf(AssetLoader::class, $this->file->loader());
        $this->assertSame($this->file->loader(), $this->file->loader());
    }

    /**
     *
     */
    public function test_export()
    {
        $this->assertEquals(
            (new Export(new Jar(__DIR__.'/../bin/ffdec.jar')))->input(__DIR__.'/_files/race3s.swf'),
            $this->file->export()
        );
    }

    /**
     *
     */
    public function test_toXml()
    {
        $xml = $this->file->toXml();
        $this->assertFileExists($xml->path());

        $this->assertSame($xml, $this->file->toXml());

        $output = __DIR__.'/_files/swf.xml';
        $newXml = $this->file->toXml($output);
        $this->assertNotSame($xml, $newXml);

        $this->assertFileExists($output);
        $this->assertEquals($output, $newXml->path());
        $this->assertSame($newXml, $this->file->toXml());

        $newXml->clear();
        $this->assertNotSame($newXml, $newXml = $this->file->toXml());

        $xml->clear();
        $newXml->clear();
    }

    /**
     *
     */
    public function test_path()
    {
        $this->assertEquals(__DIR__.'/_files/race3s.swf', $this->file->path());
    }
}
