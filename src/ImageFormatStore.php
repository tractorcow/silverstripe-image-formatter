<?php

namespace TractorCow\SilverStripeImageFormatter;

use League\Flysystem\Filesystem;
use SilverStripe\Assets\FilenameParsing\FileResolutionStrategy;
use SilverStripe\Assets\FilenameParsing\ParsedFileID;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;

class ImageFormatStore extends FlysystemAssetStore
{
    /**
     * Stop caring about hash when checking for file collisions; This breaks many things.
     * Specifically, the "original" image and the formatted image will have different hashes,
     * but we cannot determine the formatted hash without generating it, or storing it somewhere.
     *
     * @param string $filename
     * @param string $hash
     * @param null $variant
     * @return bool|mixed
     */
    public function exists($filename, $hash, $variant = null)
    {
        if (empty($filename) || empty($hash)) {
            return false;
        }

        $parsedFileID = new ParsedFileID($filename, $hash, $variant);

        $publicSet = [
            $this->getPublicFilesystem(),
            $this->getPublicResolutionStrategy(),
        ];

        $protectedSet = [
            $this->getProtectedFilesystem(),
            $this->getProtectedResolutionStrategy(),
        ];

        /** @var Filesystem $fs */
        /** @var FileResolutionStrategy $strategy */

        // First we try to search for exact file id string match
        foreach ([$publicSet, $protectedSet] as $set) {
            list($fs, $strategy) = $set;
            $fileID = $strategy->buildFileID($parsedFileID);
            if ($fs->has($fileID)) {
                return true;
            }
        }

        // Let's fall back to using our FileResolution strategy to see if our FileID matches alternative formats
        foreach ([$publicSet, $protectedSet] as $set) {
            list($fs, $strategy) = $set;
            $closesureParsedFileID = $strategy->searchForTuple($parsedFileID, $fs, false);
            if ($closesureParsedFileID) {
                return true;
            }
        }

        return null;
    }
}
