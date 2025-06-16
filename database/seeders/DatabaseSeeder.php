<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Core system data
        $this->call(CountriesSeeder::class);
        $this->call(StateSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(DefaultImagesSeeder::class);
        $this->call(ThemeSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(HomePageSeeder::class);
        $this->call(ThemeOptionSeeder::class);
        $this->call(OrderStatusSeeder::class);

        // LMS specific data
        $this->call(CategorySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(MediaSeeder::class);
        $this->call(ChapterSeeder::class);
        $this->call(QuizSeeder::class);
        $this->call(ExamSeeder::class);
        $this->call(EnrollmentSeeder::class);
        $this->call(RevenueSeeder::class);
        $this->call(QuizSessionSeeder::class);
        $this->call(CertificateSeeder::class);

        // Test data (optional)
        $this->call(TestDataSeeder::class);
    }
}
