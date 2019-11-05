<?php

namespace Swf\Cli\Export;

use PHPUnit\Framework\TestCase;
use Swf\Cli\Jar;

/**
 * Class ExportTest
 */
class ExportTest extends TestCase
{
    /**
     * @var Export
     */
    private $export;

    /**
     *
     */
    protected function setUp()
    {
        $this->export = new Export(new Jar(__DIR__.'/../../../bin/ffdec.jar'));
    }

    /**
     *
     */
    public function test_export_sprites_with_default_format()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNotNull($sprites->byId(4));
        $this->assertNotNull($sprites->byId(6));
        $this->assertNotNull($sprites->byId(9));
        $this->assertNotNull($sprites->byId(11));
        $this->assertNotNull($sprites->byId(13));
        $this->assertNotNull($sprites->byId(15));
        $this->assertNotNull($sprites->byId(22));
        $this->assertNotNull($sprites->byId(31));
        $this->assertNotNull($sprites->byId(40));
        $this->assertNotNull($sprites->byId(41));

        $this->assertEquals(Export::FORMAT_PNG, $sprites->byId(4)->frameFormat());

        $result->clear();
    }

    /**
     *
     */
    public function test_export_sprites_with_format()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE => Export::FORMAT_SVG])
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNotNull($sprites->byId(4));
        $this->assertNotNull($sprites->byId(6));
        $this->assertNotNull($sprites->byId(9));
        $this->assertNotNull($sprites->byId(11));
        $this->assertNotNull($sprites->byId(13));
        $this->assertNotNull($sprites->byId(15));
        $this->assertNotNull($sprites->byId(22));
        $this->assertNotNull($sprites->byId(31));
        $this->assertNotNull($sprites->byId(40));
        $this->assertNotNull($sprites->byId(41));

        $this->assertEquals(Export::FORMAT_SVG, $sprites->byId(4)->frameFormat());

        $result->clear();
    }

    /**
     *
     */
    public function test_export_ids_single()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(6)
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNull($sprites->byId(4));
        $this->assertNotNull($sprites->byId(6));
        $this->assertNull($sprites->byId(9));
        $this->assertNull($sprites->byId(11));
        $this->assertNull($sprites->byId(13));
        $this->assertNull($sprites->byId(15));
        $this->assertNull($sprites->byId(22));
        $this->assertNull($sprites->byId(31));
        $this->assertNull($sprites->byId(40));
        $this->assertNull($sprites->byId(41));

        $result->clear();
    }

    /**
     *
     */
    public function test_export_ids_range()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(6, 13)
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNull($sprites->byId(4));
        $this->assertNotNull($sprites->byId(6));
        $this->assertNotNull($sprites->byId(9));
        $this->assertNotNull($sprites->byId(11));
        $this->assertNotNull($sprites->byId(13));
        $this->assertNull($sprites->byId(15));
        $this->assertNull($sprites->byId(22));
        $this->assertNull($sprites->byId(31));
        $this->assertNull($sprites->byId(40));
        $this->assertNull($sprites->byId(41));

        $result->clear();
    }

    /**
     *
     */
    public function test_export_ids_string_range()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids('22-41')
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNull($sprites->byId(4));
        $this->assertNull($sprites->byId(6));
        $this->assertNull($sprites->byId(9));
        $this->assertNull($sprites->byId(11));
        $this->assertNull($sprites->byId(13));
        $this->assertNull($sprites->byId(15));
        $this->assertNotNull($sprites->byId(22));
        $this->assertNotNull($sprites->byId(31));
        $this->assertNotNull($sprites->byId(40));
        $this->assertNotNull($sprites->byId(41));

        $result->clear();
    }

    /**
     *
     */
    public function test_export_ids_array_range()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids([4, 11, '22-40'])
            ->execute()
        ;

        $sprites = $result->sprites();

        $this->assertNotNull($sprites->byId(4));
        $this->assertNull($sprites->byId(6));
        $this->assertNull($sprites->byId(9));
        $this->assertNotNull($sprites->byId(11));
        $this->assertNull($sprites->byId(13));
        $this->assertNull($sprites->byId(15));
        $this->assertNotNull($sprites->byId(22));
        $this->assertNotNull($sprites->byId(31));
        $this->assertNotNull($sprites->byId(40));
        $this->assertNull($sprites->byId(41));

        $result->clear();
    }

    /**
     *
     */
    public function test_export_select_single_frame()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(22)
            ->select(15)
            ->execute()
        ;

        $sprite = $result->sprites()->byId(22);

        $this->assertFileExists($sprite->frame(15));

        try {
            $sprite->frame(1);
            $this->fail('Expects exception');
        } catch (\RuntimeException $e) {}

        $result->clear();
    }

    /**
     *
     */
    public function test_export_select_frame_range()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(22)
            ->select(15, 18)
            ->execute()
        ;

        $sprite = $result->sprites()->byId(22);

        $this->assertFileExists($sprite->frame(15));
        $this->assertFileExists($sprite->frame(16));
        $this->assertFileExists($sprite->frame(17));
        $this->assertFileExists($sprite->frame(18));

        try {
            $sprite->frame(19);
            $this->fail('Expects exception');
        } catch (\RuntimeException $e) {}

        $result->clear();
    }

    /**
     *
     */
    public function test_export_select_frame_range_string()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(22)
            ->select('25-45')
            ->execute()
        ;

        $sprite = $result->sprites()->byId(22);

        $this->assertFileExists($sprite->frame(25));
        $this->assertFileExists($sprite->frame(35));
        $this->assertFileExists($sprite->frame(45));

        try {
            $sprite->frame(15);
            $this->fail('Expects exception');
        } catch (\RuntimeException $e) {}

        $result->clear();
    }

    /**
     *
     */
    public function test_export_select_frame_range_array()
    {
        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(22)
            ->select([4, 8, '25-45'])
            ->execute()
        ;

        $sprite = $result->sprites()->byId(22);

        $this->assertFileExists($sprite->frame(4));
        $this->assertFileExists($sprite->frame(8));
        $this->assertFileExists($sprite->frame(25));
        $this->assertFileExists($sprite->frame(35));
        $this->assertFileExists($sprite->frame(45));

        try {
            $sprite->frame(15);
            $this->fail('Expects exception');
        } catch (\RuntimeException $e) {}

        $result->clear();
    }

    /**
     *
     */
    public function test_output()
    {
        $output = __DIR__.'/_files/out';

        $result = $this->export
            ->input(__DIR__.'/../../_files/race3s.swf')
            ->itemTypes([Export::ITEM_TYPE_SPRITE])
            ->ids(4)
            ->output($output)
            ->execute()
        ;

        $sprite = $result->sprites()->byId(4);

        $this->assertDirectoryExists($output);
        $this->assertEquals($output.'/DefineSprite_4/1.png', $sprite->frame());

        $result->clear();
        $this->assertDirectoryNotExists($output);
    }
}
