<?php

namespace Swf;

use Swf\Cli\Jar;
use Swf\Processor\BulkLoader;

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

    /**
     * Creates a bulk assets loader
     *
     * @param string[] $files List of swf source files
     *
     * @return BulkLoader
     */
    public function bulk(array $files): BulkLoader
    {
        $loader = new BulkLoader($this->jar);

        foreach ($files as $file) {
            $loader->addFile($this->load($file));
        }

        return $loader;
    }
}
