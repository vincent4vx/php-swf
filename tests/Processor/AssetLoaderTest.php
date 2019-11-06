<?php

namespace Swf\Processor;

use PHPUnit\Framework\TestCase;
use Swf\Asset\Sprite;
use Swf\Cli\Export\Export;
use Swf\Cli\Export\ExportResult;
use Swf\Cli\Jar;
use Swf\SwfFile;

/**
 *
 */
class AssetLoaderTest extends TestCase
{
    /**
     * @var AssetLoader
     */
    private $loader;

    protected function setUp()
    {
        $this->loader = new AssetLoader(new SwfFile(new Jar(__DIR__.'/../../bin/ffdec.jar'), __DIR__.'/../_files/race3s.swf'));
    }

    /**
     *
     */
    public function test_resolveId()
    {
        $this->assertEquals(22, $this->loader->resolveId('race3s_fla.readySet_7'));
        $this->assertEquals(40, $this->loader->resolveId('race3s_fla.finish_10'));
        $this->assertEquals(1, $this->loader->resolveId('EngineStart'));
        $this->assertNull($this->loader->resolveId('not found'));
    }

    /**
     *
     */
    public function test_has()
    {
        $this->assertTrue($this->loader->has(4));
        $this->assertFalse($this->loader->has(404));
    }

    /**
     *
     */
    public function test_typeOf()
    {
        $this->assertEquals(Export::ITEM_TYPE_SOUND, $this->loader->typeOf(1));
        $this->assertEquals(Export::ITEM_TYPE_SPRITE, $this->loader->typeOf(4));
        $this->assertEquals(Export::ITEM_TYPE_SHAPE, $this->loader->typeOf(5));
        $this->assertNull($this->loader->typeOf(404));
    }

    /**
     *
     */
    public function test_hasNamed()
    {
        $this->assertTrue($this->loader->hasNamed('race3s_fla.readySet_7'));
        $this->assertTrue($this->loader->hasNamed('race3s_fla.finish_10'));
        $this->assertTrue($this->loader->hasNamed('EngineStart'));
        $this->assertFalse($this->loader->hasNamed('not found'));
    }

    /**
     *
     */
    public function test_get_not_found()
    {
        $this->assertNull($this->loader->get(404));
        $this->assertNull($this->loader->getFromCache(404));
    }

    /**
     *
     */
    public function test_get()
    {
        $this->assertNull($this->loader->getFromCache(4));

        $sprite = $this->loader->get(4);

        $this->assertInstanceOf(Sprite::class, $sprite);
        $this->assertEquals(641.15, $sprite->bounds()->width());
        $this->assertEquals(503.15, $sprite->bounds()->height());

        $this->assertEquals($sprite, $this->loader->getFromCache(4));
    }

    /**
     *
     */
    public function test_find_not_found()
    {
        $this->assertNull($this->loader->find('not found'));
        $this->assertNull($this->loader->findFromCache('not found'));
    }

    /**
     *
     */
    public function test_find()
    {
        $this->assertNull($this->loader->findFromCache('race3s_fla.readySet_7'));

        $sprite = $this->loader->find('race3s_fla.readySet_7');

        $this->assertInstanceOf(Sprite::class, $sprite);
        $this->assertEquals(163.90, $sprite->bounds()->width());
        $this->assertEquals(66.95, $sprite->bounds()->height());

        $this->assertEquals($sprite, $this->loader->findFromCache('race3s_fla.readySet_7'));
    }

    /**
     *
     */
    public function test_withResult()
    {
        $loader = $this->loader->withResult($result = new ExportResult(__DIR__.'/../_files/out'));

        $this->assertNotSame($loader, $this->loader);

        $sprite = $loader->get(6);

        $this->assertStringStartsWith(__DIR__.'/../_files/out', $sprite->frame());
        $this->assertDirectoryExists(__DIR__.'/../_files/out');

        $result->clear();
    }
}
