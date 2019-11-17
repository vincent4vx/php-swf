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

        $this->assertEquals([4, 6, 9, 11, 13, 15, 22, 31, 40, 41], $xml->tagsByType('DefineSprite')->map(function ($item) { return (int) $item['spriteId']; })->toArray(false));
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
        $output = '/tmp/out/swf.xml';

        $xml = $this->toXml->input(__DIR__.'/../../_files/race3s.swf')
            ->output($output)
            ->execute()
        ;

        $this->assertFileExists($output);
        $xml->clear();
        $this->assertFileNotExists($output);
    }

    /**
     *
     */
    public function test_read()
    {
        $xml = $this->toXml->input(__DIR__.'/../../_files/race3s.swf')->execute();

        $elements = iterator_to_array($xml->read(['swf', 'tags', 'item', 'names', 'item']));

        $this->assertContainsOnlyInstancesOf(\SimpleXMLElement::class, $elements);

        foreach ($elements as $element) {
            $this->assertEquals('item', $element->getName());
        }

        $this->assertEquals(['race3s_fla.readySet_7', 'EngineLoop', 'EngineStart', 'race3s_fla.MainTimeline', 'race3s_fla.finishAnimation_9', 'race3s_fla.finish_10'], array_map(function (\SimpleXMLElement $e) { return (string) $e; }, $elements));
    }
}
