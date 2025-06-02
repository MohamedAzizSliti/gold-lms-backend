<?php

namespace App\Repositories\Eloquents;


use App\Enums\MediaTypeEnum;
use App\Models\Chapter;

use Owenoj\LaravelGetId3\GetId3;

use Illuminate\Http\Request;

class ChapterRepository extends Repository
{
    public static function model()
    {
        return Chapter::class;
    }

    public static function storeByRequest(Request $request)
    {
        // dd($request->all());
        $chapter = self::create([
            'title' => $request->title,
            'serial_number' => $request->serial_number,
            'course_id' => $request->course_id,
        ]);


        foreach ($request->contents as $requestContent) {
            $isFree = false;
            $isForwardable = false;

            $contentMedia = isset($requestContent['media']) ? MediaRepository::storeByRequest(
                $requestContent['media'],
                'course/chapter/content/media',
                MediaTypeEnum::IMAGE
            ) : null;

            if (isset($requestContent['is_forwardable'])) {
                $isForwardable = !$isForwardable;
            }

            if (isset($requestContent['is_free'])) {
                $isFree =  !$isFree;
            }

            $mediaLink = $requestContent['link'] ?? null;
            $media = $requestContent['media'] ?? null;

            if ($media) {
                $mediaType = self::getFileType($media);
                $mediaDuration = self::getMediaPlaytime($media);
            } elseif ($mediaLink) {
                $mediaType = MediaTypeEnum::VIDEO;
                $mediaDuration = $requestContent['duration'];
            } else {
                throw new \Exception('No media or media link provided.');
            }


            // customize media link
            $customWidth = '100%';
            $customHeight = '450';

            // Replace the width and height attributes in the iframe
            $customizedIframe = preg_replace(
                ['/width="\d+"/', '/height="\d+"/'], // Match width and height attributes
                ["width=\"$customWidth\"", "height=\"$customHeight\""], // Replace with custom values
                $mediaLink
            );

            $mediaLink = $customizedIframe;

            ContentRepository::create([
                'chapter_id' => $chapter->id,
                'media_id' => $contentMedia ? $contentMedia->id : null,
                'title' => $requestContent['title'],
                'type' => $mediaType,
                'duration' => $mediaDuration,
                'serial_number' => $requestContent['serial_number'],
                'is_forwardable' => $isForwardable,
                'is_free' => $isFree,
                'media_link' => $mediaLink,
                'media_updated_at' => now()
            ]);
        }

        return $chapter;
    }

    public static function updateByRequest(Request $request, Chapter $chapter)
    {
        self::update($chapter, [
            'title' => $request->title ?? $chapter->title,
            'serial_number' => $request->serial_number ?? $chapter->serial_number
        ]);

        // Delete removed content
        $existingContentIds = ContentRepository::query()->where('chapter_id', $chapter->id)->pluck('id')->toArray();
        $deletedContentIds = array_diff($existingContentIds, collect($request->contents)->pluck('content_id')->toArray());

        if ($deletedContentIds) {
            ContentRepository::query()->whereIn('id', $deletedContentIds)->delete();
        }

        $newContent = false;

        // Manipulate and update the $request->contents
        foreach ($request->contents as $content) {
            $contentId = $content['content_id'] ?? 0;
            $newMedia = false;
            $mediaID = null;
            $mediaType = null;
            $duration = 0;
            $isForwardable = false;
            $isFree = false;

            if (isset($content['is_forwardable']) && $content['is_forwardable'] === 'on') {
                $isForwardable = true;
            }

            if (isset($content['is_free']) && $content['is_free'] === 'on') {
                $isFree = true;
            }

            if ($contentId) {
                // Existing content
                $existsContent = ContentRepository::find($contentId);
                $mediaID = $existsContent->media_id;
                $mediaType = $existsContent->type;
                $duration = $existsContent->duration;

                if (isset($content['media']) && !empty($content['media'])) {
                    $media = self::uploadFile($content['media']);
                    $mediaID = $media->id;
                    $mediaType = self::getFileType($content['media']);
                    $newMedia = true;
                    $duration = self::getMediaPlaytime($content['media']);

                    // Clear existing link if new media is uploaded
                    $content['link'] = null;
                }

                // Clear existing media if a new link is provided
                if (isset($content['link']) && !empty($content['link'])) {
                    $mediaID = null;
                    $mediaType = null;
                    $duration = $content['duration'];
                }
            } else {
                // New content
                if (isset($content['media']) && !empty($content['media'])) {
                    $media = self::uploadFile($content['media']);
                    $mediaID = $media->id;
                    $mediaType = self::getFileType($content['media']);
                    $newContent = true;
                    $duration = self::getMediaPlaytime($content['media']);

                    // Clear existing link if new media is uploaded
                    $content['link'] = null;
                }

                if (isset($content['link'])) {
                    $duration = $content['duration'];
                }
            }


            // customize media link
            $customWidth = '100%';
            $customHeight = '450';

            // Replace the width and height attributes in the iframe
            $customizedIframe = preg_replace(
                ['/width="\d+"/', '/height="\d+"/'], // Match width and height attributes
                ["width=\"$customWidth\"", "height=\"$customHeight\""], // Replace with custom values
                $content['link']
            );

            $content['link'] = $customizedIframe;

            // Update the content data
            $updatedContent = [
                'id' => $contentId,
                'title' => $content['title'],
                'type' => $mediaType ? $mediaType : MediaTypeEnum::VIDEO,
                'duration' => $duration,
                'serial_number' => $content['serial_number'],
                'media_id' => $mediaID,
                'media_link' => $content['link'] ?? null,
                'is_forwardable' => $isForwardable,
                'is_free' => $isFree,
                'media_updated_at' => $newMedia ? now() : ($existsContent->media_updated_at ?? null)
            ];

            // Update or create in the database
            ContentRepository::query()->updateOrCreate([
                'id' => $contentId,
                'chapter_id' => $chapter->id
            ], $updatedContent);
        }

        return $newContent;
    }


    private static function uploadFile($file)
    {
        return $file ? MediaRepository::storeByRequest(
            $file,
            'course/chapter/content/media',
            self::getFileType($file),
        ) : null;
    }

    private static function getMediaPlaytime($file)
    {
        $mediaType = self::getFileType($file);

        $minutes = 0;

        if ($mediaType == MediaTypeEnum::AUDIO || $mediaType == MediaTypeEnum::VIDEO) {
            $track = GetId3::fromUploadedFile($file);

            $time = explode(':', $track->getPlaytime());
            $minutes = (int) $time[0] ? $time[0] : 1;
        }

        return $minutes;
    }

    private static function getFileType($file)
    {
        switch ($file->getClientMimeType()) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/jpg':
            case 'image/gif':
            case 'image/svg+xml':
                $mediaType = MediaTypeEnum::IMAGE;
                break;
            case 'video/mp4':
            case 'video/mpeg':
                $mediaType = MediaTypeEnum::VIDEO;
                break;
            case 'audio/mpeg':
            case 'audio/wav':
            case 'audio/webm':
            case 'audio/ogg':
            case 'audio/x-wav':
                $mediaType = MediaTypeEnum::AUDIO;
                break;
            case 'application/pdf':
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $mediaType = MediaTypeEnum::DOCUMENT;
                break;
            default:
                $mediaType = MediaTypeEnum::IMAGE;
                break;
        }

        return $mediaType;
    }
}
