<?php

namespace App\Repositories\Eloquents;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository extends Repository
{

    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Media::class;
    }

    public static function storeByRequest(UploadedFile $file, $path, $type = null): Media
    {
       // $src = Storage::put('/' . trim($path, '/'), $file, 'public');
        $src = $file->store(trim($path, '/'), 'public');

        return self::create([
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);
    }

    // this for local path file update

    public static function storeByPath($filePath, $path, $type = null): Media
    {
        $absoluteFilePath = realpath($filePath);

        if (!$absoluteFilePath) {
            $filePath = str_replace('storage/app/public/', '', $filePath);
            $absoluteFilePath = storage_path('app/public/' . ltrim($filePath, '/'));
        }

        \Log::info("Attempting to store file by path: $filePath");
        \Log::info("Resolved absolute path: $absoluteFilePath");

        if (!file_exists($absoluteFilePath)) {
            \Log::error("File not found at resolved path: $absoluteFilePath");
            throw new \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException("File not found at path: $absoluteFilePath");
        }

        $fileContents = file_get_contents($absoluteFilePath);
        $fileName = basename($absoluteFilePath);

        $src = Storage::put('/' . trim($path, '/') . '/' . $fileName, $fileContents, 'public');

        // Return a new Media record with the stored file details
        return self::create([
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION), // Get the extension from file path
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);
    }

    public static function updateByRequest(UploadedFile $file, Media $media, $path, $type = null): Media
    {
        $src = Storage::put('/' . trim($path, '/'), $file, 'public');

        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        self::update($media, [
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);

        return $media;
    }

    public static function updateOrCreateByRequest(UploadedFile $file, $path, $media = null, $type = null): Media
    {
        $src = Storage::put('/' . trim($path, '/'), $file, 'public');

        if ($media && Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $media = self::query()->updateOrCreate([
            'id' => $media?->id ?? 0
        ], [
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);

        return $media;
    }
}
