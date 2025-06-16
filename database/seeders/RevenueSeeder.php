<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Revenue;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;

class RevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active enrollments
        $enrollments = Enrollment::where('status', 'active')
            ->with(['course.instructor'])
            ->get();

        foreach ($enrollments as $enrollment) {
            // Skip if no course or instructor
            if (!$enrollment->course || !$enrollment->course->instructor) {
                continue;
            }

            $totalAmount = $enrollment->amount_paid ?? $enrollment->course->price ?? 100;
            
            // Calculate amounts
            $charityAmount = $totalAmount * 0.03; // 3% for charity
            $platformFee = $totalAmount * 0.05;   // 5% platform fee
            $instructorAmount = $totalAmount - $charityAmount - $platformFee; // Remaining for instructor

            // Create revenue record
            Revenue::create([
                'enrollment_id' => $enrollment->id,
                'instructor_id' => $enrollment->course->user_id,
                'course_id' => $enrollment->course_id,
                'total_amount' => $totalAmount,
                'instructor_amount' => $instructorAmount,
                'platform_fee' => $platformFee,
                'charity_amount' => $charityAmount,
                'payment_date' => $enrollment->enrollment_date ?? $enrollment->created_at,
                'payment_method' => $enrollment->payment_method ?? 'credit_card',
                'payment_id' => 'pay_' . uniqid(),
                'transaction_id' => 'txn_' . uniqid(),
                'status' => 'completed',
                'created_at' => $enrollment->created_at,
                'updated_at' => $enrollment->updated_at,
            ]);
        }

        // Create additional sample revenue records for the last 6 months
        $this->createMonthlyRevenueData();
    }

    private function createMonthlyRevenueData()
    {
        $instructors = User::role('instructor')->get();
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer'];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $recordsCount = rand(5, 15); // Random number of records per month
            
            for ($j = 0; $j < $recordsCount; $j++) {
                $instructor = $instructors->random();
                $totalAmount = rand(50, 500); // Random amount between 50-500
                
                $charityAmount = $totalAmount * 0.03;
                $platformFee = $totalAmount * 0.05;
                $instructorAmount = $totalAmount - $charityAmount - $platformFee;
                
                Revenue::create([
                    'enrollment_id' => null, // Sample data without specific enrollment
                    'instructor_id' => $instructor->id,
                    'course_id' => null,
                    'total_amount' => $totalAmount,
                    'instructor_amount' => $instructorAmount,
                    'platform_fee' => $platformFee,
                    'charity_amount' => $charityAmount,
                    'payment_date' => $month->copy()->addDays(rand(1, 28)),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payment_id' => 'pay_sample_' . uniqid(),
                    'transaction_id' => 'txn_sample_' . uniqid(),
                    'status' => 'completed',
                    'created_at' => $month->copy()->addDays(rand(1, 28)),
                    'updated_at' => $month->copy()->addDays(rand(1, 28)),
                ]);
            }
        }
    }
} 