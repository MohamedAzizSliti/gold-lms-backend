<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attachment;
use App\Models\Course;
use Carbon\Carbon;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->info('No courses found. Please run CourseSeeder first.');
            return;
        }

        $this->command->info('Found ' . $courses->count() . ' courses. Creating media records...');

        // Default images for different course types
        $defaultImages = [
            'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop&crop=center',
            'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=800&h=600&fit=crop&crop=center',
            'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=800&h=600&fit=crop&crop=center',
            'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&h=600&fit=crop&crop=center',
            'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800&h=600&fit=crop&crop=center',
            'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&h=600&fit=crop&crop=center',
        ];

        foreach ($courses as $index => $course) {
            // Skip if course already has media
            if ($course->media_id) {
                $this->command->info('Course "' . $course->title . '" already has media. Skipping...');
                continue;
            }

            // Select an image based on course index
            $imageUrl = $defaultImages[$index % count($defaultImages)];
            $filename = 'course-' . $course->id . '-cover.jpg';

            try {
                $attachment = Attachment::create([
                    'name' => $course->title . ' - Cover Image',
                    'file_name' => $filename,
                    'original_url' => $imageUrl,
                    'mime_type' => 'image/jpeg',
                    'size' => 0,
                    'collection_name' => 'course_images',
                    'disk' => 'public',
                    'src' => 'course-covers/' . $filename, // Virtual path for our file serving route
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'manipulations' => '[]',
                    'custom_properties' => '[]',
                    'generated_conversions' => '[]',
                    'responsive_images' => '[]',
                    'order_column' => 0,
                    'model_type' => Course::class,
                    'model_id' => $course->id
                ]);

                // Update the course with the media ID
                $course->update(['media_id' => $attachment->id]);
                
                $this->command->info('Created media for course: ' . $course->title);
                
            } catch (\Exception $e) {
                $this->command->error('Failed to create media for course "' . $course->title . '": ' . $e->getMessage());
            }
        }

        $this->command->info('Media seeding completed!');
    }
}
