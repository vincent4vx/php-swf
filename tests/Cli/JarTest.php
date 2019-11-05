<?php

namespace Swf\Cli;

use PHPUnit\Framework\TestCase;

/**
 * Class JarTest
 */
class JarTest extends TestCase
{
    /**
     * @var Jar
     */
    private $jar;

    /**
     *
     */
    protected function setUp()
    {
        $this->jar = new Jar(__DIR__.'/../../bin/ffdec.jar');
    }

    /**
     *
     */
    public function test_default()
    {
        $this->assertEquals("java -jar '" . __DIR__.'/../../bin/ffdec.jar' . "'", (string) $this->jar);
    }

    /**
     *
     */
    public function test_argument()
    {
        $jar = $this->jar->argument('my arg');

        $this->assertNotEquals($this->jar, $jar);
        $this->assertEquals("java -jar '" . __DIR__.'/../../bin/ffdec.jar' . "' 'my arg'", (string) $jar);

        $jar = $jar->argument('arg2');
        $this->assertEquals("java -jar '" . __DIR__.'/../../bin/ffdec.jar' . "' 'my arg' 'arg2'", (string) $jar);
    }

    /**
     *
     */
    public function test_option()
    {
        $jar = $this->jar->option('foo', 'bar');

        $this->assertNotEquals($this->jar, $jar);
        $this->assertEquals("java -jar '" . __DIR__.'/../../bin/ffdec.jar' . "' -foo 'bar'", (string) $jar);

        $jar = $jar->option('opt', 'val');
        $this->assertEquals("java -jar '" . __DIR__.'/../../bin/ffdec.jar' . "' -foo 'bar' -opt 'val'", (string) $jar);
    }

    /**
     *
     */
    public function test_execute_success()
    {
        $output = $this->jar->argument('-help')->execute();

        $this->assertStringContainsString('JPEXS Free Flash Decompiler', $output);
        $this->assertStringContainsString('Commandline arguments', $output);
    }

    /**
     *
     */
    public function test_execute_error()
    {
        $this->expectException(JarException::class);
        $this->expectExceptionMessage('Error: Bad Commandline Arguments!');

        $this->jar->argument('-undefined')->execute();
    }
}
