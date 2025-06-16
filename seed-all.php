<?php

/**
 * Script to seed all data for Gold LMS
 * Run this script to populate the database with complete test data
 */

echo "🌱 Starting Gold LMS Database Seeding...\n\n";

// Check if we're in the correct directory
if (!file_exists('artisan')) {
    echo "❌ Error: Please run this script from the Laravel root directory (api folder)\n";
    exit(1);
}

// Commands to run
$commands = [
    'php artisan migrate:fresh --force' => '🔄 Refreshing database migrations...',
    'php artisan db:seed --class=CountriesSeeder' => '🌍 Seeding countries...',
    'php artisan db:seed --class=StateSeeder' => '🏛️ Seeding states...',
    'php artisan db:seed --class=RoleSeeder' => '👥 Seeding roles...',
    'php artisan db:seed --class=CurrencySeeder' => '💰 Seeding currencies...',
    'php artisan db:seed --class=DefaultImagesSeeder' => '🖼️ Seeding default images...',
    'php artisan db:seed --class=ThemeSeeder' => '🎨 Seeding themes...',
    'php artisan db:seed --class=SettingSeeder' => '⚙️ Seeding settings...',
    'php artisan db:seed --class=HomePageSeeder' => '🏠 Seeding home page...',
    'php artisan db:seed --class=ThemeOptionSeeder' => '🎭 Seeding theme options...',
    'php artisan db:seed --class=OrderStatusSeeder' => '📦 Seeding order statuses...',
    'php artisan db:seed --class=CategorySeeder' => '📚 Seeding course categories...',
    'php artisan db:seed --class=UserSeeder' => '👤 Seeding users (admin, teachers, students)...',
    'php artisan db:seed --class=CourseSeeder' => '🎓 Seeding courses...',
    'php artisan db:seed --class=MediaSeeder' => '📸 Seeding course media...',
    'php artisan db:seed --class=ChapterSeeder' => '📖 Seeding course chapters...',
    'php artisan db:seed --class=QuizSeeder' => '❓ Seeding quizzes...',
    'php artisan db:seed --class=ExamSeeder' => '📝 Seeding exams...',
    'php artisan db:seed --class=EnrollmentSeeder' => '📋 Seeding student enrollments...',
    'php artisan db:seed --class=QuizSessionSeeder' => '🎯 Seeding quiz and exam sessions...',
    'php artisan db:seed --class=CertificateSeeder' => '🏆 Seeding certificates...',
    'php artisan db:seed --class=TestDataSeeder' => '🧪 Seeding additional test data...',
];

$success = 0;
$total = count($commands);

foreach ($commands as $command => $description) {
    echo $description . "\n";
    
    // Execute command
    $output = [];
    $return_code = 0;
    exec($command . ' 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ Success\n\n";
        $success++;
    } else {
        echo "❌ Failed\n";
        echo "Error output:\n";
        foreach ($output as $line) {
            echo "   " . $line . "\n";
        }
        echo "\n";
    }
}

echo "📊 Seeding Summary:\n";
echo "✅ Successful: $success/$total\n";
echo "❌ Failed: " . ($total - $success) . "/$total\n\n";

if ($success === $total) {
    echo "🎉 All seeders completed successfully!\n\n";
    echo "📋 Test Data Summary:\n";
    echo "👤 Users:\n";
    echo "   - Admin: admin@goldlms.com (password: password123)\n";
    echo "   - Teachers: 3 teachers with various specializations\n";
    echo "   - Students: 7 students including 'Rihem Kochti'\n\n";
    
    echo "🎓 Courses:\n";
    echo "   - Formation HTML & CSS Avancé\n";
    echo "   - JavaScript ES6 Moderne\n";
    echo "   - React.js Fundamentals\n";
    echo "   - Python Programming\n";
    echo "   - Database Design\n";
    echo "   - Node.js Backend\n\n";
    
    echo "📚 Content:\n";
    echo "   - 30+ chapters across all courses\n";
    echo "   - 4 quizzes with multiple questions\n";
    echo "   - 4 comprehensive exams\n";
    echo "   - 15+ student enrollments\n";
    echo "   - 10+ quiz/exam sessions\n";
    echo "   - 6 certificates for completed courses\n\n";
    
    echo "🏆 Certificate Features:\n";
    echo "   - Students with 100% course completion have certificates\n";
    echo "   - Certificate ID format: CERT-ESSECT-XXXXXXXX\n";
    echo "   - Includes student name, course, score, and completion date\n";
    echo "   - Ready for 'Voir le certificat' functionality\n\n";
    
    echo "🚀 Ready to test!\n";
    echo "   - Login as student: rihem.kochti@student.com (password: student123)\n";
    echo "   - Go to 'Mes Inscriptions' to see enrolled courses\n";
    echo "   - Click 'Voir le certificat' for completed courses\n";
    
} else {
    echo "⚠️ Some seeders failed. Please check the errors above.\n";
    echo "You may need to:\n";
    echo "1. Check database connection\n";
    echo "2. Ensure all migrations are up to date\n";
    echo "3. Verify model relationships\n";
}

echo "\n🔚 Seeding process completed.\n";
?>
