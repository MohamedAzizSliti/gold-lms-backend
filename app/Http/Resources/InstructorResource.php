<?php

namespace App\Http\Resources;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reviews = Instructor::with('courses.reviews')
            ->find($this->id)
            ->courses->flatMap(function ($course) {
                return $course->reviews;
            });

        $averageRating = $reviews->avg('rating');
        $reviewsCount = $reviews->count();

        $totalEnrollments = Instructor::with('courses.enrollments')
            ->find($this->id)
            ->courses->sum(function ($course) {
                return $course->enrollments->count();
            });

        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'profile_picture' => $this->user->profilePicturePath,
            'title' => $this->title,
            'about' => $this->about,
            'is_featured' => $this->is_featured,
            'joining_date' => $this->created_at->format('d M, Y'),
            'average_rating' => (float) number_format($averageRating, 1) ?? 0.0,
            'reviews_count' => $reviewsCount,
            'course_count' => $this->courses->count(),
            'student_count' => $totalEnrollments,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
        ];
    }
}
