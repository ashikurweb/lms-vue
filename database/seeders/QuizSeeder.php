<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or Create Category
        $category = Category::firstOrCreate(
            ['slug' => 'development'],
            [
                'name' => 'Development',
                'description' => 'Software development courses',
                'is_active' => true,
                'is_featured' => true,
                'color' => '#6366f1'
            ]
        );

        // 2. Get Instructor
        $instructor = User::role('instructor')->first() ?: User::factory()->create()->assignRole('instructor');

        // 3. Create Course
        $course = Course::create([
            'uuid' => (string) Str::uuid(),
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Mastering Laravel Quizzes',
            'slug' => 'mastering-laravel-quizzes-' . rand(100, 999),
            'short_description' => 'A comprehensive course to test your Laravel knowledge.',
            'description' => 'This course is specifically designed for testing the quiz system implementation.',
            'level' => 'intermediate',
            'language' => 'English',
            'price_type' => 'free',
            'is_published' => true,
            'is_approved' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Enroll the instructor themselves as a student for testing
        Enrollment::create([
            'user_id' => $instructor->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        // 4. Create Section
        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Laravel Core Concepts',
            'order' => 1,
            'is_published' => true,
        ]);

        // 5. Create Quiz
        $quiz = Quiz::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'lesson_id' => null, // Standalone quiz
            'title' => 'Laravel Fundamentals Quiz',
            'description' => 'Test your basic understanding of Laravel routing, controllers, and Eloquent.',
            'instructions' => 'Read each question carefully. You have 10 minutes to complete this quiz.',
            'time_limit' => 10,
            'passing_score' => 70,
            'max_attempts' => 3,
            'questions_per_page' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_answers_after_submission' => true,
            'show_correct_answers' => true,
            'allow_review' => true,
            'is_required' => true,
            'is_published' => true,
        ]);

        // 6. Create Questions
        $questions = [
            [
                'type' => 'single_choice',
                'question' => 'What is the correct way to define a route that accepts a parameter in Laravel?',
                'explanation' => 'Route parameters are always encased within curly braces {}.',
                'points' => 5,
                'options' => [
                    ['text' => 'Route::get("/user/:id")', 'correct' => false],
                    ['text' => 'Route::get("/user/{id}")', 'correct' => true],
                    ['text' => 'Route::get("/user/$id")', 'correct' => false],
                    ['text' => 'Route::get("/user/[id]")', 'correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question' => 'Eloquent ORM uses Active Record pattern.',
                'explanation' => 'Eloquent provides a simple ActiveRecord implementation for working with your database.',
                'points' => 5,
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Which of the following are valid Laravel Middlewares?',
                'explanation' => 'auth and throttle are built-in middlewares in Laravel.',
                'points' => 10,
                'options' => [
                    ['text' => 'auth', 'correct' => true],
                    ['text' => 'throttle', 'correct' => true],
                    ['text' => 'database', 'correct' => false],
                    ['text' => 'encrypt', 'correct' => false],
                ]
            ],
            [
                'type' => 'short_answer',
                'question' => 'What command is used to create a new controller?',
                'explanation' => "The full command is 'php artisan make:controller Name'.",
                'points' => 10,
                'options' => [
                    ['text' => 'make:controller', 'correct' => true],
                    ['text' => 'php artisan make:controller', 'correct' => true],
                ]
            ],
            [
                'type' => 'matching',
                'question' => 'Match the following Laravel concepts:',
                'explanation' => 'Blade is for UI, Eloquent for DB, and Artisan for CLI.',
                'points' => 15,
                'options' => [
                    ['text' => 'Blade', 'match' => 'Templating Engine', 'correct' => false],
                    ['text' => 'Eloquent', 'match' => 'PHP ORM', 'correct' => false],
                    ['text' => 'Artisan', 'match' => 'CLI Tool', 'correct' => false],
                ]
            ]
        ];

        foreach ($questions as $index => $qData) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'type' => $qData['type'],
                'question' => $qData['question'],
                'explanation' => $qData['explanation'],
                'points' => $qData['points'],
                'order' => $index + 1,
                'is_required' => true,
            ]);

            foreach ($qData['options'] as $oIndex => $oData) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $oData['text'],
                    'match_with' => $oData['match'] ?? null,
                    'is_correct' => $oData['correct'],
                    'order' => $oIndex + 1,
                ]);
            }
        }

        // Update Quiz Stats
        $quiz->updateStats();

        $this->command->info('Quiz system seeded successfully!');
    }
}
