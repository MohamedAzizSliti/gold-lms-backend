<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Développement Web',
                'description' => 'Cours de développement web frontend et backend',
                'slug' => 'developpement-web',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Programmation',
                'description' => 'Langages de programmation et concepts fondamentaux',
                'slug' => 'programmation',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Framework',
                'description' => 'Frameworks et bibliothèques de développement',
                'slug' => 'framework',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Base de Données',
                'description' => 'Conception et gestion de bases de données',
                'slug' => 'base-de-donnees',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'DevOps',
                'description' => 'Déploiement, CI/CD et infrastructure',
                'slug' => 'devops',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Mobile',
                'description' => 'Développement d\'applications mobiles',
                'slug' => 'mobile',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Design',
                'description' => 'UI/UX Design et design graphique',
                'slug' => 'design',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sécurité',
                'description' => 'Cybersécurité et sécurité informatique',
                'slug' => 'securite',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Category data seeded successfully!');
    }
}
