<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Cloudinary\Cloudinary;

trait ImageUploadTrait
{
    use LoggableTrait;

    protected $cloudinary;

    public function __contructor()
    {
        $this->cloudinary = new Cloudinary();
    }

    public function uploadImage($image, $folder = null)
    {
        try {
            if (!$image->isValid()) {
                return null;
            }

            $uploadResult = $this->cloudinary
                ->uploadApi()
                ->upload($image->getRealPath(), [
                    'folder' => $folder,
                    'public_id' => Str::random(10),
                ]);

            $imageUrl = $uploadResult['secure_url'] ?? null;

            return $imageUrl;
        } catch (\Exception $e) {
            $this->logError($e);

            return null;
        }
    }

    public function multipleUploadImage(array $images, $folder = null)
    {
        try {
            $uploadImages = [];

            foreach ($images as $image) {
                $uploadResule = $this->uploadImage($image, $folder);

                if ($uploadResule) {
                    $uploadImages[] = $uploadResule;
                }
            }

            return $uploadImages;
        } catch (\Exception $e) {
            $this->logError($e);

            return null;
        }
    }

    public function deleteImage($imageUrl, $folder = null)
    {
        try {
            $publicId = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_FILENAME);
            $publicIdWithFolder = $folder . '/' . $publicId;

            $deleteResult = $this->cloudinary
                ->uploadApi()
                ->destroy($publicIdWithFolder);

            if ($deleteResult['result'] === 'ok') {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return false;
        }
    }

    public function deleteMultipleImage(array $images, $folder = null)
    {
        try {
            $deleteResults = [];

            foreach ($images as $image) {
                $deleteResult = $this->deleteImage($image, $folder);

                $deleteResults[] = $deleteResult;
            }

            return $deleteResults;
        } catch (\Exception $e) {
            $this->logError($e);

            return null;
        }
    }
}
