<?php

namespace Swf\Cli\ToXml;

use Swf\Cli\Jar;

/**
 * Converts the <infile> SWF to <outfile> XML file
 */
final class ToXml
{
    /**
     * @var Jar
     */
    private $jar;

    /**
     * The input SWF file
     *
     * @var string
     */
    private $input;

    /**
     * The output XML file
     *
     * @var string|null
     */
    private $output;


    /**
     * ToXml constructor.
     *
     * @param Jar $jar
     */
    public function __construct(Jar $jar)
    {
        $this->jar = $jar;
    }

    /**
     * Define the input file
     *
     * @param string $input
     *
     * @return $this
     */
    public function input(string $input): ToXml
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Define the output file
     * If null is passed, a temporary file will be generated
     *
     * @param string|null $output
     *
     * @return $this
     */
    public function output(?string $output): ToXml
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Execute the command
     *
     * @return SwfXml The created XML
     */
    public function execute(): SwfXml
    {
        $output = $this->output ?: tempnam(sys_get_temp_dir(), 'swf_xml_');

        $this->jar
            ->option('swf2xml', $this->input)
            ->argument($output)
            ->execute()
        ;

        return new SwfXml($output);
    }
}
