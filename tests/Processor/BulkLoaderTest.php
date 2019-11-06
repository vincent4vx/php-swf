<?php

namespace Swf\Processor;

use PHPUnit\Framework\TestCase;
use Swf\Asset\Sprite;
use Swf\Cli\Export\ExportResult;
use Swf\SwfLoader;

/**
 * Class BulkLoaderTest
 */
class BulkLoaderTest extends TestCase
{
    /**
     * @var BulkLoader
     */
    private $loader;

    /**
     *
     */
    protected function setUp()
    {
        $this->loader = (new SwfLoader())->bulk([__DIR__.'/../_files/race3s.swf']);
    }

    /**
     *
     */
    public function test_load()
    {
        $this->assertNull($this->loader->get('race3s_fla.readySet_7'));

        $sprites = $this->loader
            ->add('race3s_fla.readySet_7')
            ->add('race3s_fla.finish_10')
            ->add('not found')
            ->load()
        ;

        $this->assertCount(2, $sprites);
        $this->assertContainsOnlyInstancesOf(Sprite::class, $sprites);
        $this->assertArrayHasKey('race3s_fla.readySet_7', $sprites);
        $this->assertArrayHasKey('race3s_fla.finish_10', $sprites);

        $this->assertSame($sprites['race3s_fla.readySet_7'], $this->loader->get('race3s_fla.readySet_7'));
    }

    /**
     *
     */
    public function test_load_already_loaded()
    {
        $sprites = $this->loader
            ->add('race3s_fla.readySet_7')
            ->add('race3s_fla.finish_10')
            ->load()
        ;

        $this->assertCount(2, $sprites);
        $this->assertContainsOnlyInstancesOf(Sprite::class, $sprites);
        $this->assertArrayHasKey('race3s_fla.readySet_7', $sprites);
        $this->assertArrayHasKey('race3s_fla.finish_10', $sprites);

        $secondCall = $this->loader
            ->add('race3s_fla.readySet_7')
            ->add('race3s_fla.finishAnimation_9')
            ->load()
        ;

        $this->assertCount(2, $secondCall);
        $this->assertContainsOnlyInstancesOf(Sprite::class, $secondCall);
        $this->assertArrayHasKey('race3s_fla.readySet_7', $secondCall);
        $this->assertArrayHasKey('race3s_fla.finishAnimation_9', $secondCall);
        $this->assertSame($sprites['race3s_fla.readySet_7'], $secondCall['race3s_fla.readySet_7']);
    }

    /**
     *
     */
    public function test_setResultDirectory()
    {
        $this->loader
            ->setResultDirectory(__DIR__.'/../_files/out')
            ->add('race3s_fla.readySet_7')
            ->load()
        ;

        $this->assertDirectoryExists(__DIR__.'/../_files/out/race3s.swf');
        $this->assertStringStartsWith(__DIR__.'/../_files/out/race3s.swf', $this->loader->get('race3s_fla.readySet_7')->frame());

        $inCache = (new SwfLoader())->bulk([__DIR__.'/../_files/race3s.swf'])
            ->setResultDirectory(__DIR__.'/../_files/out')
            ->get('race3s_fla.readySet_7')
        ;

        $this->assertEquals($this->loader->get('race3s_fla.readySet_7')->frame(), $inCache->frame());

        (new ExportResult(__DIR__.'/../_files/out'))->clear();
    }

    /**
     *
     */
    public function test_addFile()
    {
        $sprites = $this->loader
            ->setResultDirectory(__DIR__.'/../_files/out')
            ->addFile((new SwfLoader())->load(__DIR__.'/../_files/slime.swf'))
            ->add('race3s_fla.readySet_7')
            ->add('WaterCircle')
            ->load()
        ;

        $this->assertCount(2, $sprites);

        $this->assertDirectoryExists(__DIR__.'/../_files/out/race3s.swf');
        $this->assertDirectoryExists(__DIR__.'/../_files/out/slime.swf');
        $this->assertStringStartsWith(__DIR__.'/../_files/out/race3s.swf', $this->loader->get('race3s_fla.readySet_7')->frame());
        $this->assertStringStartsWith(__DIR__.'/../_files/out/slime.swf', $this->loader->get('WaterCircle')->frame());

        (new ExportResult(__DIR__.'/../_files/out'))->clear();
    }
}
