<?php

namespace TractorCow\SilverStripeImageFormatter;

use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;

/**
 * @property File|DBFile $owner
 */
class ImageFormatExtension extends Extension
{
    use Configurable;

    private static $default_quality = 80;
    private static $jpg_quality = 80;
    private static $webp_quality = 80;
    private static $avif_quality = 50;

    /**
     * Create a variant of the image in a different format.
     *
     * @param string $newExtension The file extension of the formatted file, e.g. "webp"
     */
    public function Format(string $newExtension): ?DBFile
    {
        $original = $this->getOwner();
        return $original->manipulateExtension(
            $newExtension,
            function (AssetStore $store, string $filename, string $hash, string $variant) use ($original, $newExtension) {
                $backend = clone $original->getImageBackend();
                // If backend isn't available
                if (!$backend || !$backend->getImageResource()) {
                    return null;
                }
                // get quality for current conversion
                $quality = $this->config()->default_quality;
                if ($newExtension == 'avif') {
                    $quality = $this->config()->avif_quality;
                } elseif ($newExtension == 'webp') {
                    $quality = $this->config()->webp_quality;
                } elseif ($newExtension == 'jpg' || $newExtension == 'jpeg') {
                    $quality = $this->config()->jpg_quality;
                }
                // use existing backend quality if explicitely set lower (e.g. for placeholder images)
                $quality = min($quality, $backend->getQuality());
                $backend->setQuality($quality);
                $config = ['conflict' => AssetStore::CONFLICT_USE_EXISTING];
                $tuple = $backend->writeToStore($store, $filename, $hash, $variant, $config);
                return [$tuple, $backend];
            }
        );
    }
}
