<?php

namespace Database\Seeders;

use App\Models\AIExamGeneration;
use App\Models\B2BEnquiry;
use App\Models\B2BEnquiryItem;
use App\Models\Category;
use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\Institute;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\StudentAnswer;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super_admin', 'admin', 'institute_admin', 'teacher', 'student'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $institute = Institute::create([
            'name' => 'NETS Coaching Institute',
            'slug' => 'nets-coaching',
            'description' => 'National Education Technology System - Coaching Institute',
            'address' => '123 Education Street, Delhi, India',
            'phone' => '1800-123-4567',
            'email' => 'info@nets.com',
            'website' => 'https://nets.com',
            'is_active' => true,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@nets.com'],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@nets.com',
                'phone' => '9876543210',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        if (! $instituteAdmin->hasRole('institute_admin')) {
            $instituteAdmin->assignRole('institute_admin');
        }

        if (! $teacher->hasRole('teacher')) {
            $teacher->assignRole('teacher');
        }

        if (! $student1->hasRole('student')) {
            $student1->assignRole('student');
        }

        if (! $student2->hasRole('student')) {
            $student2->assignRole('student');
        }

        $instituteAdmin = User::firstOrCreate(
            ['email' => 'institute@nets.com'],
            [
                'name' => 'Institute Manager',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'institute@nets.com',
                'phone' => '9876543211',
                'role' => 'institute',
                'institute_id' => $institute->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@nets.com'],
            [
                'name' => 'Teacher Kumar',
                'first_name' => 'Raj',
                'last_name' => 'Kumar',
                'email' => 'teacher@nets.com',
                'phone' => '9876543212',
                'role' => 'teacher',
                'institute_id' => $institute->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $student1 = User::firstOrCreate(
            ['email' => 'student1@nets.com'],
            [
                'name' => 'Student One',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'student1@nets.com',
                'phone' => '9876543213',
                'role' => 'student',
                'institute_id' => $institute->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $student2 = User::firstOrCreate(
            ['email' => 'student2@nets.com'],
            [
                'name' => 'Student Two',
                'first_name' => 'Bob',
                'last_name' => 'Williams',
                'email' => 'student2@nets.com',
                'phone' => '9876543214',
                'role' => 'student',
                'institute_id' => $institute->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH101', 'description' => 'Advanced Mathematics'],
            ['name' => 'Physics', 'code' => 'PHY101', 'description' => 'Basic Physics'],
            ['name' => 'Chemistry', 'code' => 'CHE101', 'description' => 'Organic Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO101', 'description' => 'Cell Biology'],
            ['name' => 'English', 'code' => 'ENG101', 'description' => 'English Literature'],
        ];

        foreach ($subjects as $subjectData) {
            $subject = Subject::create(array_merge($subjectData, [
                'institute_id' => $institute->id,
                'is_active' => true,
                'created_by' => $admin->id,
            ]));

            $topics = [];
            if ($subject['name'] === 'Mathematics') {
                $topics = ['Algebra', 'Calculus', 'Geometry', 'Trigonometry'];
            } elseif ($subject['name'] === 'Physics') {
                $topics = ['Mechanics', 'Thermodynamics', 'Electromagnetism', 'Optics'];
            } elseif ($subject['name'] === 'Chemistry') {
                $topics = ['Organic', 'Inorganic', 'Physical', 'Analytical'];
            } elseif ($subject['name'] === 'Biology') {
                $topics = ['Cell Biology', 'Genetics', 'Ecology', 'Evolution'];
            } elseif ($subject['name'] === 'English') {
                $topics = ['Grammar', 'Literature', 'Comprehension', 'Writing'];
            }

            foreach ($topics as $topicName) {
                Topic::create([
                    'subject_id' => $subject->id,
                    'name' => $topicName,
                    'description' => $topicName . ' topics for ' . $subject['name'],
                    'standard_marks' => 1,
                    'is_active' => true,
                ]);
            }
        }

        for ($i = 1; $i <= 30; $i++) {
            $subject = Subject::inRandomOrder()->first();
            $topic = $subject->topics()->inRandomOrder()->first();

            $question = Question::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'question_text' => 'What is ' . $i . ' + ' . ($i * 2) . '?',
                'question_type' => 'mcq',
                'subject_id' => $subject->id,
                'topic_id' => $topic ? $topic->id : null,
                'difficulty' => ['easy', 'medium', 'hard'][array_rand(['easy', 'medium', 'hard'])],
                'marks' => 1,
                'negative_marks' => 0,
                'explanation' => 'This is a basic arithmetic question.',
                'status' => 'published',
                'is_ai_generated' => false,
                'correct_answer_text' => null,
                'institute_id' => $institute->id,
                'created_by' => $teacher->id,
            ]);

            $correctOption = ($i % 4) + 1;
            for ($j = 1; $j <= 4; $j++) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => 'Option ' . $j . ' for question ' . $i,
                    'option_value' => chr(64 + $j),
                    'is_correct' => ($j === $correctOption),
                    'sort_order' => $j,
                ]);
            }
        }

        for ($i = 1; $i <= 10; $i++) {
            $subject = Subject::inRandomOrder()->first();
            Question::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'question_text' => 'What is the value of x in the equation ' . $i . 'x = ' . ($i * 5) . '?',
                'question_type' => 'integer',
                'subject_id' => $subject->id,
                'difficulty' => 'medium',
                'marks' => 2,
                'negative_marks' => -1,
                'explanation' => 'Solve for x.',
                'status' => 'published',
                'is_ai_generated' => false,
                'correct_answer_text' => '5',
                'institute_id' => $institute->id,
                'created_by' => $teacher->id,
            ]);
        }

        $exam = Examination::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'test_id' => 'TEST-2026-00001',
            'title' => 'Mathematics Sample Test',
            'description' => 'A sample test for Mathematics covering Algebra and Calculus.',
            'type' => 'cbt',
            'subject_id' => Subject::where('name', 'Mathematics')->first()->id,
            'duration' => 60,
            'total_marks' => 40,
            'passing_marks' => 16,
            'max_attempts' => 3,
            'negative_marking' => true,
            'negative_marks_ratio' => 0.25,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_result_immediately' => true,
            'result_visibility' => 'immediately',
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(7),
            'is_active' => true,
            'is_published' => true,
            'institute_id' => $institute->id,
            'created_by' => $teacher->id,
        ]);

        $mathSubject = Subject::where('name', 'Mathematics')->first();
        $questions = Question::where('subject_id', $mathSubject->id)->where('status', 'published')->get();
        foreach ($questions as $index => $question) {
            \App\Models\ExamQuestion::create([
                'examination_id' => $exam->id,
                'question_id' => $question->id,
                'sort_order' => $index + 1,
                'marks' => $question->marks,
            ]);
        }

        $exam2 = Examination::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'test_id' => 'TEST-2026-00002',
            'title' => 'Physics Sample Test',
            'description' => 'A sample test for Physics covering Mechanics.',
            'type' => 'cbt',
            'subject_id' => Subject::where('name', 'Physics')->first()->id,
            'duration' => 45,
            'total_marks' => 20,
            'passing_marks' => 8,
            'max_attempts' => 1,
            'negative_marking' => false,
            'negative_marks_ratio' => 0,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_result_immediately' => true,
            'result_visibility' => 'immediately',
            'start_time' => now()->subDays(2),
            'end_time' => now()->addDays(5),
            'is_active' => true,
            'is_published' => true,
            'institute_id' => $institute->id,
            'created_by' => $teacher->id,
        ]);

        $physicsSubject = Subject::where('name', 'Physics')->first();
        $physicsQuestions = Question::where('subject_id', $physicsSubject->id)->where('status', 'published')->limit(10)->get();
        foreach ($physicsQuestions as $index => $question) {
            \App\Models\ExamQuestion::create([
                'examination_id' => $exam2->id,
                'question_id' => $question->id,
                'sort_order' => $index + 1,
                'marks' => $question->marks,
            ]);
        }

        $categories = [
            ['name' => 'Textbooks', 'slug' => 'textbooks', 'description' => 'Complete textbooks and guides'],
            ['name' => 'Practice Tests', 'slug' => 'practice-tests', 'description' => 'Practice test papers'],
            ['name' => 'Video Lectures', 'slug' => 'video-lectures', 'description' => 'Recorded video lectures'],
            ['name' => 'Study Notes', 'slug' => 'study-notes', 'description' => 'Quick reference notes'],
        ];

        foreach ($categories as $catData) {
            Category::create($catData);
        }

        $materials = [
            ['title' => 'Mathematics Complete Guide', 'type' => 'pdf', 'is_paid' => true, 'price' => 999.00],
            ['title' => 'Physics Fundamentals', 'type' => 'pdf', 'is_paid' => true, 'price' => 799.00],
            ['title' => 'Chemistry Quick Reference', 'type' => 'pdf', 'is_paid' => false, 'price' => 0.00],
            ['title' => 'Biology Lecture Notes', 'type' => 'document', 'is_paid' => true, 'price' => 599.00],
            ['title' => 'English Grammar Guide', 'type' => 'pdf', 'is_paid' => false, 'price' => 0.00],
        ];

        for ($i = 0; $i < count($materials); $i++) {
            $material = $materials[$i];
            $subject = Subject::skip($i)->first();
            $studyMaterial = StudyMaterial::create([
                'title' => $material['title'],
                'description' => 'Comprehensive study material for ' . $material['title'],
                'subject_id' => $subject ? $subject->id : null,
                'type' => $material['type'],
                'file_path' => 'study-materials/sample-' . $material['type'] . '.pdf',
                'thumbnail' => null,
                'is_paid' => $material['is_paid'],
                'price' => $material['price'],
                'is_published' => true,
                'download_count' => 0,
                'institute_id' => $institute->id,
                'uploaded_by' => $teacher->id,
            ]);

            if ($i < 3) {
                $studyMaterial->categories()->attach([$categories[$i]['slug'] ? Category::where('slug', $categories[$i]['slug'])->first()->id : null]);
            }
        }

        $order = Order::create([
            'order_number' => 'ORD-2026-00001',
            'user_id' => $student1->id,
            'total_amount' => 999.00,
            'discount_amount' => 0,
            'tax_amount' => 180.00,
            'currency' => 'INR',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'transaction_id' => 'txn_12345',
            'ordered_at' => now(),
        ]);

        $paidMaterial = StudyMaterial::where('is_paid', true)->first();
        if ($paidMaterial) {
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'study_material',
                'item_id' => $paidMaterial->id,
                'item_name' => $paidMaterial->title,
                'quantity' => 1,
                'unit_price' => 999.00,
                'total_price' => 999.00,
            ]);
        }

        $attempt = ExamAttempt::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $student1->id,
            'examination_id' => $exam->id,
            'attempt_number' => 1,
            'start_time' => now()->subMinutes(45),
            'end_time' => now()->subMinutes(15),
            'status' => 'completed',
            'total_marks' => 40,
            'obtained_marks' => 24,
            'percentage' => 60.0,
            'is_passed' => true,
            'time_taken' => 1800,
        ]);

        Result::create([
            'attempt_id' => $attempt->id,
            'total_questions' => 40,
            'attempted_questions' => 35,
            'correct_answers' => 24,
            'incorrect_answers' => 11,
            'unanswered_questions' => 5,
            'total_marks' => 40,
            'obtained_marks' => 24,
            'percentage' => 60.0,
            'time_taken' => 1800,
            'rank_position' => 1,
        ]);

        B2BEnquiry::create([
            'enquiry_number' => 'ENQ-2026-ABC123',
            'name' => 'Rohan Sharma',
            'email' => 'rohan@school.edu',
            'phone' => '9876500001',
            'company' => 'Delhi Public School',
            'designation' => 'Purchase Manager',
            'subject' => 'Bulk study material inquiry',
            'description' => 'We require 500 copies of Mathematics and Science study materials for our students.',
            'status' => 'new',
            'institute_id' => $institute->id,
        ]);
    }
}
