<?php

namespace Swf\Cli;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class InstallerTest
 *
 * @group installer
 */
class InstallerTest extends TestCase
{
    /**
     * @var Installer
     */
    private $installer;

    protected function setUp()
    {
        $this->installer = new Installer();
        $this->installer->setTarget(__DIR__.'/_tmp');
    }

    protected function tearDown()
    {
        /** @var \SplFileInfo[] $it */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/_tmp', RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($it as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir(__DIR__.'/_tmp');
    }

    /**
     *
     */
    public function test()
    {
        $this->installer->setVersion('version11.2.0');

        $this->assertFalse($this->installer->installed());

        $this->installer->install();
        $this->assertTrue($this->installer->installed());
        $this->assertDirectoryExists(__DIR__.'/_tmp');
        $this->assertFileExists(__DIR__.'/_tmp/ffdec.jar');
        $this->assertEquals('version11.2.0', file_get_contents(__DIR__.'/_tmp/version'));

        $this->installer->setVersion('version11.1.0');
        $this->assertFalse($this->installer->installed());
    }
}
