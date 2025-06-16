# Gold LMS Database Seeders

Ce dossier contient tous les seeders pour peupler la base de données Gold LMS avec des données de test complètes.

## 📋 Liste des Seeders

### 🔧 Seeders Système (Core)
- **CountriesSeeder** - Pays et régions
- **StateSeeder** - États/provinces
- **RoleSeeder** - Rôles utilisateurs (admin, teacher, student)
- **CurrencySeeder** - Devises
- **DefaultImagesSeeder** - Images par défaut
- **ThemeSeeder** - Thèmes de l'application
- **SettingSeeder** - Paramètres système
- **HomePageSeeder** - Contenu page d'accueil
- **ThemeOptionSeeder** - Options de thème
- **OrderStatusSeeder** - Statuts de commande

### 🎓 Seeders LMS (Learning Management System)
- **CategorySeeder** - Catégories de cours
- **UserSeeder** - Utilisateurs (admin, enseignants, étudiants)
- **CourseSeeder** - Cours avec détails complets
- **MediaSeeder** - Images et médias des cours
- **ChapterSeeder** - Chapitres des cours
- **QuizSeeder** - Quiz avec questions
- **ExamSeeder** - Examens avec questions
- **EnrollmentSeeder** - Inscriptions étudiants
- **QuizSessionSeeder** - Sessions de quiz/examens
- **CertificateSeeder** - Certificats de réussite

### 🧪 Seeders Test
- **TestDataSeeder** - Données de test supplémentaires

## 🚀 Utilisation

### Méthode 1: Script Automatique (Recommandé)
```bash
cd api
php seed-all.php
```

### Méthode 2: Commandes Laravel
```bash
cd api

# Réinitialiser et migrer
php artisan migrate:fresh

# Exécuter tous les seeders
php artisan db:seed

# Ou exécuter un seeder spécifique
php artisan db:seed --class=UserSeeder
```

### Méthode 3: Seeders Individuels
```bash
# Seeders système
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UserSeeder

# Seeders LMS
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=EnrollmentSeeder
php artisan db:seed --class=CertificateSeeder
```

## 📊 Données Créées

### 👥 Utilisateurs
- **1 Admin**: admin@goldlms.com (password: password123)
- **3 Enseignants**:
  - Prof. Ahmed Benali (ahmed.benali@goldlms.com)
  - Prof. Fatma Khelifi (fatma.khelifi@goldlms.com)
  - Prof. Mohamed Trabelsi (mohamed.trabelsi@goldlms.com)
- **7 Étudiants**:
  - Rihem Kochti (rihem.kochti@student.com)
  - Ahmed Benali (ahmed.benali@student.com)
  - Fatma Khelifi (fatma.khelifi@student.com)
  - Mohamed Trabelsi (mohamed.trabelsi@student.com)
  - Lina Touati (lina.touati@student.com)
  - Youssef Mansouri (youssef.mansouri@student.com)
  - Amina Rahali (amina.rahali@student.com)

*Mot de passe pour tous: `student123` ou `teacher123`*

### 🎓 Cours
1. **Formation HTML & CSS Avancé** (150 DT) - Prof. Ahmed Benali
2. **JavaScript ES6 Moderne** (200 DT) - Prof. Fatma Khelifi
3. **React.js Fundamentals** (180 DT) - Prof. Mohamed Trabelsi
4. **Python Programming** (220 DT) - Prof. Ahmed Benali
5. **Database Design** (190 DT) - Prof. Fatma Khelifi
6. **Node.js Backend** (250 DT) - Prof. Mohamed Trabelsi

### 📚 Contenu
- **30+ Chapitres** répartis sur tous les cours
- **4 Quiz** avec questions à choix multiples
- **4 Examens** de certification
- **15+ Inscriptions** d'étudiants
- **10+ Sessions** de quiz/examens complétées
- **6 Certificats** pour les cours terminés

### 🏆 Certificats
Les étudiants suivants ont des certificats disponibles:
- **Rihem Kochti**: HTML & CSS, React.js
- **Ahmed Benali**: Python Programming
- **Fatma Khelifi**: Database Design
- **Mohamed Trabelsi**: Node.js Backend
- **Lina Touati**: React.js

## 🎯 Test de la Fonctionnalité Certificat

### Étapes de Test:
1. **Connexion**: rihem.kochti@student.com (password: student123)
2. **Navigation**: Aller dans "Mes Inscriptions"
3. **Certificat**: Cliquer sur "Voir le certificat" pour les cours terminés
4. **Vérification**: Le certificat s'affiche avec le design Gold LMS

### Données Certificat Rihem Kochti:
- **Nom**: Rihem Kochti
- **Cours**: Formation HTML & CSS Avancé / React.js Fundamentals
- **Score**: 95% / 92%
- **Évaluation**: 3/3 Réussi
- **ID**: CERT-ESSECT-XXXXXXXX
- **Organisation**: GOLD LMS
- **Direction**: DIRECTION ESSECT

## 🔧 Dépendances

### Ordre d'Exécution Important:
1. **RoleSeeder** → Avant UserSeeder
2. **CategorySeeder** → Avant CourseSeeder
3. **UserSeeder** → Avant CourseSeeder
4. **CourseSeeder** → Avant ChapterSeeder, QuizSeeder, ExamSeeder
5. **EnrollmentSeeder** → Avant QuizSessionSeeder, CertificateSeeder
6. **QuizSeeder, ExamSeeder** → Avant QuizSessionSeeder

## 🐛 Dépannage

### Erreurs Communes:
- **Foreign Key Constraint**: Vérifier l'ordre des seeders
- **Duplicate Entry**: Exécuter `migrate:fresh` avant seeding
- **Class Not Found**: Vérifier les imports dans les modèles

### Solutions:
```bash
# Réinitialiser complètement
php artisan migrate:fresh
php artisan db:seed

# Vérifier les migrations
php artisan migrate:status

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear
```

## 📝 Notes

- Tous les mots de passe sont hashés avec bcrypt
- Les dates sont générées avec Carbon pour cohérence
- Les IDs de certificat sont uniques et aléatoires
- Les données sont en français pour correspondre au contexte tunisien
- Les images utilisent des URLs Unsplash pour les tests

## 🔄 Mise à Jour

Pour ajouter de nouvelles données:
1. Modifier le seeder approprié
2. Exécuter: `php artisan db:seed --class=NomDuSeeder`
3. Ou réexécuter tous les seeders avec le script `seed-all.php`
