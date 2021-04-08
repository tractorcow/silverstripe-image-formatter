<?php

namespace TractorCow\SilverStripeImageFormatter;

use BadMethodCallException;
use Exception;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;

/**
 * @property File|DBFile $owner
 */
class ImageFormatExtension extends DataExtension
{
    /**
     * Get a new image with the given format (file extension)
     *
     * Note: For the sake of treating a new format as a "variant" we retain the original file hash.
     *
     * @param string $format New file extension
     * @return File|DBFile
     */
    public function Format($format)
    {
        if (!$this->owner->getIsImage()) {
            throw new BadMethodCallException("Format can only be called on images");
        }

        // Can't convert if it doesn't exist
        if (!$this->owner->exists()) {
            return null;
        }

        // Skip if file converted to same extension
        $extension = $this->owner->getExtension();
        if (strcasecmp($extension, $format) === 0) {
            return $this->owner;
        }

        // Check asset details
        $filename = $this->owner->getFilename();
        $newFilename = substr($filename, 0, -strlen($extension)) . strtolower($format);
        $hash = $this->owner->getHash();
        $variant = $this->owner->getVariant();

        // Create this asset in the store if it doesn't already exist,
        // otherwise use the existing variant
        /** @var AssetStore $store */
        $store = Injector::inst()->get(AssetStore::class);
        $backend = null;
        if ($store->exists($newFilename, $hash, $variant)) {
            $tuple = [
                'Filename' => $newFilename,
                'Hash'     => $hash,
                'Variant'  => $variant
            ];
        } elseif (!$this->owner->getAllowGeneration()) {
            // Circumvent image generation if disabled
            return null;
        } else {
            // Ask intervention to re-save in a new format
            /** @var Image_Backend $backend */
            $backend = $this->owner->getImageBackend();
            if (!$backend || !$backend->getImageResource()) {
                throw new BadMethodCallException("Could not load image {$filename}");
            }

            // Immediately save to new filename
            // Normal asset visibility doesn't work for images with different filenames, so
            // save to public.
            $tuple = $backend->writeToStore(
                $store,
                $newFilename,
                $hash,
                $variant,
                [
                    'conflict'   => AssetStore::CONFLICT_USE_EXISTING,
                    'visibility' => AssetStore::VISIBILITY_PUBLIC,
                ]
            );
            if (!$tuple) {
                throw new Exception("Could not convert image {$filename} to {$newFilename}");
            }
        }

        // Store result in new DBFile instance
        /** @var DBFile $file */
        $file = DBField::create_field('DBFile', $tuple);
        $file->setOriginal($this->owner);

        // Pass the manipulated image backend down to the resampled image - this allows chained manipulations
        // without having to re-load the image resource from the manipulated file written to disk
        if ($backend) {
            $file->setImageBackend($backend);
        }

        return $file;
    }
}
