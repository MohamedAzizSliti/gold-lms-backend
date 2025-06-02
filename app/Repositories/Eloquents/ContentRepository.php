<?php

namespace App\Repositories\Eloquents;


use App\Enums\MediaTypeEnum;
use Illuminate\Http\Request;
use App\Models\Content;

class ContentRepository extends Repository
{
    public static function model()
    {
        return Content::class;
    }

    public static function storeByRequest(Request $request)
    {
        $media = $request->hasFile('media') ? MediaRepository::storeByRequest(
            $request->file('media'),
            'course/chapter/content/media',
            MediaTypeEnum::IMAGE
        ) : null;

        return self::create([
            'chapter_id' => $request->chapter_id,
            'media_id' => $media ? $media->id : null,
            'title' => $request->title,
            'type' => $request->type,
            'serial_number' => $request->serial_number
        ]);
    }

    public static function updateByRequest(Request $request, Content $content)
    {
        if ($content->media) {
            $media = $request->hasFile('media') ? MediaRepository::updateByRequest(
                $request->file('media'),
                $content->media,
                'course/chapter/content/media',
                MediaTypeEnum::IMAGE
            ) : $content->image;
        } else {
            $media = $request->hasFile('media') ? MediaRepository::storeByRequest(
                $request->file('media'),
                'course/chapter/content/media',
                MediaTypeEnum::IMAGE
            ) : null;
        }

        return self::update($content, [
            'media_id' => $media ? $media->id : null,
            'title' => $request->title ?? $content->title,
            'type' => $request->type ?? $content->type,
            'serial_number' => $request->serial_number ?? $content->serial_number
        ]);
    }
}
