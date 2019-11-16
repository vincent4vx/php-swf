<?php

namespace Swf\Cli;

use Composer\Script\Event;
use RuntimeException;

/**
 * Install FFDEC into the bin/ directory
 */
final class Installer
{
    const BASE_URL = 'https://api.github.com/repos/{owner}/{repos}/releases/tags/{version}';

    /**
     * @var string
     */
    private $owner = 'jindrapetrik';

    /**
     * @var string
     */
    private $repos = 'jpexs-decompiler';

    /**
     * @var string
     */
    private $version = 'nightly1722';

    /**
     * @var string
     */
    private $target = __DIR__.'/../../bin';

    /**
     * Check if FFDEC is already installed
     *
     * @return bool
     */
    public function installed(): bool
    {
        return
            file_exists($this->target . DIRECTORY_SEPARATOR . 'ffdec.jar')
            && file_exists($this->target . DIRECTORY_SEPARATOR . 'version')
            && file_get_contents($this->target . DIRECTORY_SEPARATOR . 'version') === $this->version
        ;
    }

    /**
     * Install FFDEC
     * Note: If FFDEC is already installed, it will be overridden
     */
    public function install(): void
    {
        if (!is_dir($this->target)) {
            if (!mkdir($this->target, 0755, true)) {
                throw new RuntimeException('Cannot create installation directory');
            }
        }

        $zip = $this->download();
        $this->unzip($zip);
        $this->setInstalledVersion();
    }

    /**
     * Change the github repository
     *
     * @param string $owner The repository owner
     * @param string $repos The repository name
     *
     * @return $this
     */
    public function setRepos(string $owner, string $repos): Installer
    {
        $this->owner = $owner;
        $this->repos = $repos;

        return $this;
    }

    /**
     * Change the installed version
     *
     * @param string $version
     *
     * @return $this
     */
    public function setVersion(string $version): Installer
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Change the installation directory
     *
     * @param string $target
     *
     * @return $this
     */
    public function setTarget(string $target): Installer
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get the github release zip asset
     *
     * @return array
     */
    private function getZipAsset(): array
    {
        $ch = curl_init(strtr(self::BASE_URL, [
            '{owner}' => $this->owner,
            '{repos}' => $this->repos,
            '{version}' => $this->version,
        ]));

        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => 'php-swf-installer',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!is_array($data)) {
            throw new RuntimeException('Invalid response from github API');
        }

        foreach ($data['assets'] as $asset) {
            if ($asset['content_type'] === 'application/zip') {
                return $asset;
            }
        }

        throw new RuntimeException('Cannot find any valid zip asset');
    }

    /**
     * Download the zip file
     *
     * @return string The zip file path
     */
    private function download(): string
    {
        $asset = $this->getZipAsset();
        $zipFile = $this->target . DIRECTORY_SEPARATOR . $asset['name'];

        copy($asset['browser_download_url'], $zipFile, stream_context_create(['http' => ['user_agent' => 'php-swf-installer']]));

        return $zipFile;
    }

    /**
     * Unzip the downloaded zip
     *
     * @param string $zipFile
     */
    private function unzip(string $zipFile): void
    {
        if (!$zip = zip_open($zipFile)) {
            throw new RuntimeException('Cannot open the zip file');
        }

        try {
            while ($entry = zip_read($zip)) {
                try {
                    $file = zip_entry_name($entry);
                    $target = $this->target . DIRECTORY_SEPARATOR . $file;

                    if ($file[-1] === '/') {
                        if (!is_dir($target)) {
                            mkdir($target, 0755, true);
                        }

                        continue;
                    }

                    if (zip_entry_open($zip, $entry, 'r')) {
                        file_put_contents($target, zip_entry_read($entry, zip_entry_filesize($entry)));
                    }
                } finally {
                    zip_entry_close($entry);
                }
            }
        } finally {
            zip_close($zip);
        }
    }

    /**
     * Save the installed version
     */
    private function setInstalledVersion(): void
    {
        file_put_contents($this->target . DIRECTORY_SEPARATOR . 'version', $this->version);
    }

    /**
     * Run the composer script
     *
     * @param Event $event
     */
    static public function run(Event $event): void
    {
        $installer = new self();

        $config = $event->getComposer()->getConfig();

        if ($config->has('ffdec.version')) {
            $installer->setVersion($config->get('ffdec.version'));
        }

        $installer->install();
    }
}
