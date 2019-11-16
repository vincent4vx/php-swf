<?php

use Swf\Cli\Installer;

require_once __DIR__.'/../vendor/autoload.php';

$installer = new Installer();

if (!$installer->installed()) {
    $installer->install();
}
