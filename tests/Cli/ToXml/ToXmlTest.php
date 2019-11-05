<?php

namespace Swf\Cli\ToXml;

use PHPUnit\Framework\TestCase;
use Swf\Cli\Jar;

/**
 * Class ToXmlTest
 */
class ToXmlTest extends TestCase
{
    /**
     * @var ToXml
     */
    private $toXml;

    /**
     *
     */
    protected function setUp()
    {
        $this->toXml = new ToXml(new Jar(__DIR__.'/../../../bin/ffdec.jar'));
    }

    /**
     *
     */
    public function test_execute()
    {
        $xml = $this->toXml->input(__DIR__.'/../../_files/race3s.swf')->execute();

        $this->assertEquals([4, 6, 9, 11, 13, 15, 22, 31, 40, 41], array_map(function ($item) { return (int) $item['spriteId']; }, $xml->tagsByType('DefineSprite')));
        $this->assertEquals([
            'race3s_fla.readySet_7' => 22,
            'EngineLoop' => 2,
            'EngineStart' => 1,
            'race3s_fla.MainTimeline' => 0,
            'race3s_fla.finishAnimation_9' => 41,
            'race3s_fla.finish_10' => 40,
        ], $xml->symbolClass());

        $xml->clear();
    }

    /**
     *
     */
    public function test_with_output()
    {
        $output = __DIR__.'/../../_files/swf.xml';

        $xml = $this->toXml->input(__DIR__.'/../../_files/race3s.swf')
            ->output($output)
            ->execute()
        ;

        $this->assertFileExists($output);
        $xml->clear();
        $this->assertFileNotExists($output);
    }
}
