<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and instructors
        $webDevCategory = Category::where('name', 'Développement Web')->first();
        $programmingCategory = Category::where('name', 'Programmation')->first();
        $frameworkCategory = Category::where('name', 'Framework')->first();
        $databaseCategory = Category::where('name', 'Base de Données')->first();

        $instructors = User::whereHas('roles', function($query) {
            $query->where('name', 'teacher');
        })->get();

        $courses = [
            [
                'title' => 'Formation HTML & CSS Avancé',
                'description' => 'Maîtrisez les fondamentaux du développement web avec HTML5 et CSS3. Apprenez à créer des sites web modernes et responsives.',
                'slug' => Str::slug('Formation HTML & CSS Avancé'),
                'price' => 150.00,
                'level' => 'intermediate',
                'language' => 'fr',
                'duration' => 2400, // 40 hours in minutes
                'category_id' => $webDevCategory?->id ?? 1,
                'user_id' => $instructors->where('name', 'Prof. Ahmed Benali')->first()?->id ?? $instructors->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => true,
                'requirements' => 'Connaissances de base en informatique',
                'what_you_will_learn' => 'HTML5, CSS3, Flexbox, Grid, Responsive Design',
                'created_at' => Carbon::now()->subDays(60),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'JavaScript ES6 Moderne',
                'description' => 'Découvrez JavaScript moderne avec ES6+. Apprenez les concepts avancés et les meilleures pratiques.',
                'slug' => Str::slug('JavaScript ES6 Moderne'),
                'price' => 200.00,
                'level' => 'advanced',
                'language' => 'fr',
                'duration' => 3000, // 50 hours in minutes
                'category_id' => $programmingCategory?->id ?? 2,
                'user_id' => $instructors->where('name', 'Prof. Fatma Khelifi')->first()?->id ?? $instructors->skip(1)->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => true,
                'requirements' => 'Connaissances de base en JavaScript',
                'what_you_will_learn' => 'ES6+, Promises, Async/Await, Modules, Classes',
                'created_at' => Carbon::now()->subDays(50),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'React.js Fundamentals',
                'description' => 'Apprenez React.js de zéro. Créez des applications web interactives avec la bibliothèque la plus populaire.',
                'slug' => Str::slug('React.js Fundamentals'),
                'price' => 180.00,
                'level' => 'intermediate',
                'language' => 'fr',
                'duration' => 2700, // 45 hours in minutes
                'category_id' => $frameworkCategory?->id ?? 3,
                'user_id' => $instructors->where('name', 'Prof. Mohamed Trabelsi')->first()?->id ?? $instructors->skip(2)->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => true,
                'requirements' => 'JavaScript ES6, HTML, CSS',
                'what_you_will_learn' => 'React Components, JSX, Hooks, State Management',
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Python Programming',
                'description' => 'Programmation Python complète. De la syntaxe de base aux concepts avancés de programmation orientée objet.',
                'slug' => Str::slug('Python Programming'),
                'price' => 220.00,
                'level' => 'beginner',
                'language' => 'fr',
                'duration' => 3600, // 60 hours in minutes
                'category_id' => $programmingCategory?->id ?? 2,
                'user_id' => $instructors->where('name', 'Prof. Ahmed Benali')->first()?->id ?? $instructors->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => false,
                'requirements' => 'Aucun prérequis',
                'what_you_will_learn' => 'Syntaxe Python, OOP, Modules, Gestion des erreurs',
                'created_at' => Carbon::now()->subDays(70),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'title' => 'Database Design',
                'description' => 'Conception et gestion de bases de données relationnelles. MySQL, PostgreSQL et bonnes pratiques.',
                'slug' => Str::slug('Database Design'),
                'price' => 190.00,
                'level' => 'intermediate',
                'language' => 'fr',
                'duration' => 2100, // 35 hours in minutes
                'category_id' => $databaseCategory?->id ?? 4,
                'user_id' => $instructors->where('name', 'Prof. Fatma Khelifi')->first()?->id ?? $instructors->skip(1)->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => false,
                'requirements' => 'Connaissances de base en informatique',
                'what_you_will_learn' => 'SQL, Normalisation, Indexation, Optimisation',
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Node.js Backend',
                'description' => 'Développement backend avec Node.js. APIs REST, Express.js, et intégration de bases de données.',
                'slug' => Str::slug('Node.js Backend'),
                'price' => 250.00,
                'level' => 'advanced',
                'language' => 'fr',
                'duration' => 3300, // 55 hours in minutes
                'category_id' => $programmingCategory?->id ?? 2,
                'user_id' => $instructors->where('name', 'Prof. Mohamed Trabelsi')->first()?->id ?? $instructors->skip(2)->first()?->id,
                'status' => 'published',
                'is_published' => true,
                'is_featured' => true,
                'requirements' => 'JavaScript ES6, Bases de données',
                'what_you_will_learn' => 'Node.js, Express.js, APIs REST, MongoDB',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }

        $this->command->info('Course data seeded successfully!');
    }
}
