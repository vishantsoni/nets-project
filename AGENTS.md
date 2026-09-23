# NETS - Educational Platform

## Project Overview

NETS is a comprehensive Study Material, E-Commerce, B2B Query Management, and Online Examination System built with Laravel 11 and Filament v3.

## Tech Stack

- **PHP**: 8.3
- **Framework**: Laravel 11
- **Admin Panel**: Filament v3
- **Role/Permissions**: Spatie Laravel Permission + Filament Shield
- **Database**: MySQL 8.4
- **Frontend**: Vite, Tailwind CSS
- **Testing**: PHPUnit

## Environment Setup

### PHP
- PHP path: See `php` in PATH or use full path at `C:\Users\Khushbu Sharma\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe`
- Required extensions: bcmath, curl, dom, fileinfo, gd, mbstring, openssl, pdo_mysql, zip

### MySQL
- Host: 127.0.0.1
- Port: 3306
- Database: nets
- User: root
- Password: (empty)

### Commands

```bash
# Run server
php artisan serve

# Run migrations
php artisan migrate:fresh --seed

# Generate Shield permissions
php artisan shield:generate --panel=admin --all

# Create Filament user
php artisan make:filament-user

# Run tests
php artisan test

# Build frontend assets
npm run build  # or npm run dev for development
```

## Architecture

### Roles
- **Admin**: Full system access (admin panel)
- **Institute**: Coaching institute admin
- **Teacher**: Creates exams and questions
- **Student**: Takes exams, purchases materials

### Panels
1. **Admin Panel** (`/admin`): Full CRUD for all entities, Shield-protected
2. **Student Panel** (`/student`): Exam attempt, results, study materials

### Public Routes
- `/`: Home page
- `/b2b-enquiry`: B2B enquiry form
- `/store`: E-commerce storefront
- `/contact`: Contact form
- `/login`, `/register`: Auth routes

### Models
- User, Institute, Subject, Topic, Question, QuestionOption
- Examination, ExamSection, ExamQuestion, ExamAttempt, StudentAnswer
- Category, StudyMaterial, Order, OrderItem, CartItem
- B2BEnquiry, B2BEnquiryItem, OMRSheet, OMRResult, Result
- PDFExtraction, AIExamGeneration

### Key Workflows

#### Examination Creation
1. Admin/Teacher creates an Examination
2. Adds questions (manual, PDF extraction, or AI generation)
3. System generates unique Test ID (e.g., TEST-2026-00001)
4. Publishes the exam

#### Exam Attempt
1. Student selects available exam
2. System creates ExamAttempt, starts timer
3. Student answers questions (MCQ, integer, true/false, etc.)
4. On submit, system auto-evaluates and generates Result
5. Student sees instant results

#### E-Commerce
1. Student browses study materials
2. Adds to cart
3. Checkout with payment (Razorpay)
4. Downloads purchased materials

#### B2B Enquiry
1. Visitor submits enquiry form
2. Admin reviews and assigns
3. Follow-up status management

### File Storage
- `storage/app/public/study-materials/`: Study material files
- `storage/app/public/omr-sheets/`: OMR sheet images
- `storage/app/public/logs/`: System logs
