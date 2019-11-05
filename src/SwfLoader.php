<?php

namespace Swf;

use Swf\Cli\Jar;

/**
 * Load Swf files
 */
final class SwfLoader
{
    /**
     * @var Jar
     */
    private $jar;


    /**
     * SwfLoader constructor.
     *
     * @param Jar $jar
     */
    public function __construct(Jar $jar = null)
    {
        $this->jar = $jar ?: new Jar(__DIR__.'/../bin/ffdec.jar');
    }

    /**
     * Load the swf file
     *
     * @param string $filename
     *
     * @return SwfFile
     */
    public function load(string $filename): SwfFile
    {
        return new SwfFile($this->jar, $filename);
    }
}
