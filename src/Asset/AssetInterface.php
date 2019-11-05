<?php

namespace Swf\Asset;

/**
 * Interface AssetInterface
 * @package Swf\Asset
 */
interface AssetInterface
{
    /**
     * Get the asset character id
     *
     * @return int
     */
    public function id(): int;

    /**
     * Get the asset type
     *
     * @return string
     */
    public function type(): string;
}
