# 📚 PAUD Application - Complete Feature Documentation

## 🎯 Overview

Aplikasi manajemen PAUD (Pendidikan Anak Usia Dini) berbasis Laravel 12 + Inertia.js + Vue 3 dengan fitur AI untuk generate narasi raport menggunakan Google Gemini.

---

## 👥 User Roles & Access

### 1. Admin (admin)
- Manajemen penuh sistem
- Kelola tahun ajaran, semester, kelas
- Kelola user (guru, orang tua)
- Kelola siswa dan aspek penilaian

### 2. Teacher/Guru (teacher)
- Input penilaian siswa
- Generate raport dengan AI
- Publish raport untuk orang tua
- Lihat statistik kelas

### 3. Parent/Orang Tua (parent)
- Lihat profil anak
- Lihat raport yang sudah dipublish
- Download raport PDF

---

## 🗺️ Complete Route Map

### Authentication Routes (/)

#### Guest Routes

```
GET  /                          # Landing page (redirect based on role)
GET  /login                     # Login page (admin/teacher)
POST /login                     # Handle login
GET  /register                  # Register page
POST /register                  # Handle registration
GET  /forgot-password           # Forgot password page
POST /forgot-password           # Send reset link
GET  /reset-password/{token}    # Reset password page
POST /reset-password            # Handle password reset
```

#### Parent Authentication Routes

```
GET  /parent/login              # Parent login page
POST /parent/login              # Handle parent login
POST /parent/logout             # Parent logout
```

#### Authenticated Routes

```
POST /logout                    # Logout (all roles)
GET  /profile                   # User profile page
PATCH /profile                  # Update profile
DELETE /profile                 # Delete account
```

---

### Admin Routes (/admin/*)

**Middleware**: `auth`, `role:admin`

#### Dashboard

```
GET /admin/dashboard            # Admin dashboard with statistics
```

#### Academic Year Management

```
GET    /admin/academic-years           # List all academic years
GET    /admin/academic-years/create    # Create form
POST   /admin/academic-years           # Store new year
GET    /admin/academic-years/{id}/edit # Edit form
PUT    /admin/academic-years/{id}      # Update year
DELETE /admin/academic-years/{id}      # Delete year
POST   /admin/academic-years/{id}/activate # Set as active year
```

#### Academic Term Management (Semesters)

```
GET    /admin/academic-terms           # List all terms
GET    /admin/academic-terms/create    # Create form
POST   /admin/academic-terms           # Store new term
GET    /admin/academic-terms/{id}/edit # Edit form
PUT    /admin/academic-terms/{id}      # Update term
DELETE /admin/academic-terms/{id}      # Delete term
```

#### Classroom Management

```
GET    /admin/classrooms           # List all classrooms
GET    /admin/classrooms/create    # Create form
POST   /admin/classrooms           # Store new classroom
GET    /admin/classrooms/{id}/edit # Edit form
PUT    /admin/classrooms/{id}      # Update classroom
DELETE /admin/classrooms/{id}      # Delete classroom
```

#### Student Management

```
GET    /admin/students             # List all students
GET    /admin/students/create      # Create form
POST   /admin/students             # Store new student
GET    /admin/students/{id}        # View student details
GET    /admin/students/{id}/edit   # Edit form
PUT    /admin/students/{id}        # Update student
DELETE /admin/students/{id}        # Delete student (soft delete)
```

#### Teacher Management

```
GET    /admin/teachers             # List all teachers
GET    /admin/teachers/create      # Create form
POST   /admin/teachers             # Store new teacher
GET    /admin/teachers/{id}/edit   # Edit form
PUT    /admin/teachers/{id}        # Update teacher
DELETE /admin/teachers/{id}        # Deactivate teacher
```

#### Assessment Aspect Management

```
GET  /admin/assessment-aspects           # List all aspects
GET  /admin/assessment-aspects/create    # Create form
POST /admin/assessment-aspects           # Store new aspect
GET  /admin/assessment-aspects/{id}/edit # Edit form
PUT  /admin/assessment-aspects/{id}      # Update aspect
DELETE /admin/assessment-aspects/{id}    # Delete aspect
POST /admin/assessment-aspects/reorder   # Update ordering
```

#### Dapodik Import (Data Pokok Pendidikan)

```
GET  /admin/dapodik/import       # Import form
POST /admin/dapodik/import       # Process import from Excel
```

---

### Teacher Routes (/teacher/*)

**Middleware**: `auth`, `role:teacher`

#### Dashboard

```
GET /teacher/dashboard           # Teacher dashboard with class overview
```

#### Student Assessments (Penilaian)

```
GET  /teacher/assessments                    # Select class/term for assessment
GET  /teacher/assessments/{classroom}/{term} # Assessment form
POST /teacher/assessments                    # Save assessments
```

#### Report Cards (Raport)

```
GET  /teacher/reports                              # Select class/term
GET  /teacher/reports/statistics                   # Statistics dashboard ⭐
GET  /teacher/reports/{classroom}/{term}/students  # List students with reports
GET  /teacher/reports/{student}/{term}             # Preview report
GET  /teacher/reports/{student}/{term}/assess      # Assessment input form
POST /teacher/reports/{student}/{term}/assess      # Save assessment data
GET  /teacher/reports/{student}/{term}/pdf         # Preview PDF in browser
POST /teacher/reports/{student}/{term}/publish     # Publish report (draft → published)
GET  /teacher/reports/{student}/{term}/download    # Download PDF
```

#### AI Narrative Generation

```
POST /teacher/reports/{student}/{term}/generate-narrative/{aspect}
     # Generate single narrative (throttle: 20/min)

POST /teacher/reports/{student}/{term}/generate-narratives
     # Generate bulk narratives (throttle: 5/min)
```

#### Growth Records (Catatan Tumbuh Kembang)

```
GET    /teacher/growth-records               # List growth records
GET    /teacher/growth-records/create        # Create form
POST   /teacher/growth-records               # Store record
GET    /teacher/growth-records/{id}/edit     # Edit form
PUT    /teacher/growth-records/{id}          # Update record
DELETE /teacher/growth-records/{id}          # Delete record
```

#### Daily Logs (Catatan Harian)

```
GET    /teacher/daily-logs                        # List daily logs
GET    /teacher/daily-logs/create                 # Create form
POST   /teacher/daily-logs                        # Store log
GET    /teacher/daily-logs/{id}/edit              # Edit form
PUT    /teacher/daily-logs/{id}                   # Update log
DELETE /teacher/daily-logs/{id}                   # Delete log
GET    /teacher/daily-logs/students-by-classroom  # AJAX: Get students by classroom
```

#### Class Journals (Jurnal Kelas)

```
GET    /teacher/class-journals           # List journals
POST   /teacher/class-journals           # Store journal
PUT    /teacher/class-journals/{id}      # Update journal
DELETE /teacher/class-journals/{id}      # Delete journal
```

---

### Parent Routes (/parent/*)

**Middleware**: `auth`, `parent`

#### Dashboard & Student Info

```
GET /parent/dashboard                           # List all children
GET /parent/students/{student}                  # Student profile + report list
GET /parent/students/{student}/reports/{term}   # View published report card
GET /parent/students/{student}/reports/{term}/download # Download PDF
```

---

## 🛠️ Services & Components

### 1. NarrativeGeneratorService

**File**: `app/Services/NarrativeGeneratorService.php`

**Purpose**: Generate AI narratives for report cards using Google Gemini API

#### Methods

```php
// Generate single narrative
generateNarrative(
    string $studentName,
    string $aspectName,
    string $aspectCategory,
    string $score,           // BB, MB, BSH, BSB
    ?string $keywords = null
): ?string

// Generate bulk narratives for all aspects
generateBulkNarratives(
    array $assessments,      // Array of assessments
    string $studentName
): array
```

#### Features

- ✅ Connects to Google Gemini Pro API
- ✅ Custom prompts for each score level (BB/MB/BSH/BSB)
- ✅ Supports keywords for personalization
- ✅ Error handling (401, 429, network errors)
- ✅ Logging for debugging
- ✅ Timeout: 30 seconds
- ✅ Safety settings configured

#### Error Messages

- **API key Gemini tidak valid** - 401/403 errors
- **Rate limit API Gemini tercapai** - 429 errors
- **Gagal terhubung ke Gemini API** - Network errors

---

### 2. PDF Generation Service

**Library**: `barryvdh/laravel-dompdf`

**Template**: `resources/views/pdf/report_card.blade.php`

#### Features

- ✅ Professional A4 portrait layout
- ✅ School header with branding
- ✅ Student information table
- ✅ Assessment scores table
- ✅ Score legend (BB, MB, BSH, BSB)
- ✅ Narrative descriptions
- ✅ Signature section (Parent, Teacher, Principal)
- ✅ Print date footer

#### Usage

```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('pdf.report_card', [
    'student' => $student,
    'academicTerm' => $academicTerm,
    'reportCard' => $reportCard,
    'reportDetails' => $reportDetails,
]);

$pdf->setPaper('a4', 'portrait');
return $pdf->download($filename);
```

---

## 📊 Database Schema

### Core Tables

#### 1. users - Multi-role users

```
- id, name, email, password
- role: enum('admin', 'teacher', 'parent')
- phone_number, address, photo_url
- teacher_competency, teacher_motto (for teachers)
- is_active, created_at, updated_at
```

#### 2. academic_years - Tahun ajaran

```
- id, name (e.g., "2024/2025")
- start_date, end_date
- is_active (only one active)
- created_at, updated_at
```

#### 3. academic_terms - Semester

```
- id, academic_year_id
- name (e.g., "Semester 1")
- semester: enum('ganjil', 'genap')
- start_date, end_date
- is_active
- created_at, updated_at
```

#### 4. classrooms - Kelas

```
- id, name, level
- academic_year_id, teacher_id
- capacity, description
- created_at, updated_at
```

#### 5. students - Data siswa

```
- id, nisn, name, nickname
- date_of_birth, place_of_birth
- gender, address, photo_url
- classroom_id
- enrollment_date, status
- notes
- created_at, updated_at, deleted_at (soft delete)
```

#### 6. parent_student - Pivot table (Many-to-Many)

```
- user_id (parent)
- student_id
- relationship_type (e.g., "father", "mother")
- is_primary_contact
- created_at, updated_at
```

---

### Assessment Tables

#### 7. assessment_aspects - Aspek penilaian

```
- id, name, category
- description
- order (for sorting) ⭐
- is_active ⭐
- created_at, updated_at
```

**6 Main Categories**:
1. Nilai Agama dan Moral (NAM)
2. Fisik Motorik
3. Kognitif
4. Bahasa
5. Sosial Emosional
6. Seni

#### 8. report_cards - Raport

```
- id, student_id, academic_term_id
- status: enum('draft', 'published')
- created_by (teacher_id)
- reviewed_by (admin_id)
- published_at
- notes
- created_at, updated_at
```

#### 9. report_details - Detail penilaian raport

```
- id, report_card_id
- assessment_aspect_id
- score: enum('BB', 'MB', 'BSH', 'BSB')
- keywords (for AI generation)
- narrative (generated or manual)
- created_at, updated_at
```

**Score Meanings**:
- **BB**: Belum Berkembang
- **MB**: Mulai Berkembang
- **BSH**: Berkembang Sesuai Harapan
- **BSB**: Berkembang Sangat Baik

---

### Observation Tables

#### 10. student_daily_logs - Catatan harian

```
- id, student_id, classroom_id
- date, attendance_status
- mood, activities, notes
- recorded_by (teacher_id)
- created_at, updated_at
```

#### 11. growth_records - Catatan tumbuh kembang

```
- id, student_id
- measurement_date
- height, weight, head_circumference
- notes
- recorded_by (teacher_id)
- created_at, updated_at
```

#### 12. class_journals - Jurnal kelas

```
- id, classroom_id
- date, title, description
- activities, notes
- photos (JSON)
- created_by (teacher_id)
- created_at, updated_at
```

#### 13. student_assessments - Penilaian siswa

```
- id, student_id, academic_term_id
- assessment_aspect_id
- score, notes
- assessed_by (teacher_id)
- created_at, updated_at
```

---

### System Tables

#### 14. prompt_templates - Template prompt AI

```
- id, name, category
- template (text)
- variables (JSON)
- is_active
- created_at, updated_at
```

---

## 🎨 Frontend Architecture

### Technology Stack

- **Vue 3** - Composition API with `<script setup>`
- **Inertia.js** - Server-side routing with SPA feel
- **TailwindCSS** - Utility-first CSS
- **Vite** - Frontend build tool

### Layout Components

- **AuthenticatedLayout** - For admin/teacher
- **TeacherLayout** - Specific for teachers
- **Parent layouts** - Inline in each page

### Key Vue Pages

#### Admin Pages (`resources/js/Pages/Admin/`)

```
Dashboard.vue              # Admin dashboard
AcademicYears/Index.vue    # Academic year list
AcademicTerms/Index.vue    # Semester list
Classrooms/Index.vue       # Classroom list
Students/Index.vue         # Student list
Teachers/Index.vue         # Teacher list
AssessmentAspects/Index.vue # Aspect list
```

#### Teacher Pages (`resources/js/Pages/Teacher/`)

```
Dashboard.vue              # Teacher dashboard
Reports/
  ├── Index.vue           # Select class/term
  ├── Students.vue        # Student list with reports
  ├── Assess.vue          # Assessment input form ⭐
  └── Statistics.vue      # Statistics dashboard ⭐
GrowthRecords/Index.vue   # Growth records
DailyLogs/Index.vue       # Daily logs
```

#### Parent Pages (`resources/js/Pages/Parent/`)

```
Login.vue                 # Parent login ⭐
Dashboard.vue             # List children ⭐
StudentProfile.vue        # Student details ⭐
ReportCard.vue            # View report card ⭐
```

---

## ⭐ Key Features Breakdown

### 1. AI-Powered Narrative Generation

#### How it works:

1. Teacher inputs score (BB/MB/BSH/BSB)
2. Optionally adds keywords (e.g., "mandiri, aktif")
3. Clicks "Generate Narasi AI"
4. System calls Gemini API with custom prompt
5. AI generates contextual narrative in Indonesian
6. Teacher can edit or regenerate

#### Bulk Generation:

- "Generate Semua Narasi" button
- Processes all aspects at once
- Rate limited: 5 requests/minute
- Shows progress for each aspect

#### Example Narrative:

```
Ahmad Fadli menunjukkan perkembangan yang sangat baik dalam
aspek Kognitif. Dia mampu mengenal konsep bilangan 1-10 dengan
mandiri dan percaya diri. Ahmad juga gemar bertanya tentang
berbagai hal di sekitarnya, menunjukkan rasa ingin tahu yang tinggi.
```

---

### 2. Report Card Publishing Workflow

#### Step-by-Step:

**1. Draft Phase:**
- Teacher selects student + semester
- Inputs scores for all aspects
- Generates/writes narratives
- Can save and edit multiple times

**2. Validation:**
- All aspects must have scores
- All aspects must have narratives
- Shows specific missing aspects if incomplete

**3. Publish:**
- Confirmation dialog
- Changes status: draft → published
- Sets published_at timestamp
- Form becomes read-only

**4. Parent Access:**
- Only published reports visible
- Parents can view + download PDF

---

### 3. Statistics Dashboard ⭐

**Location**: `/teacher/reports/statistics`

#### Features:

**Filter by classroom + semester**

**Summary cards:**
- Total students
- Published reports
- Draft reports
- Not started

**Score Distribution:**
- Visual progress bars
- BB, MB, BSH, BSB counts
- Percentage calculations

**Aspect Performance Table:**
- All aspects with breakdown
- Weighted average per aspect
- Color-coded performance (green/blue/yellow/red)

#### Weighted Average Calculation:

```
BSB = 4 points
BSH = 3 points
MB  = 2 points
BB  = 1 point

Average = Total Points / Total Assessments
```

---

### 4. Parent Portal ⭐

#### Features:

- Separate login page (`/parent/login`)
- Multi-child support
- View all children in cards
- Student profile with report history
- View published reports only
- Download PDF copies
- Read-only access (secure)

#### Security:

- Parent middleware verification
- Only access own children
- Only see published reports
- Cannot edit any data

---

## 🔒 Security Features

### 1. Authentication & Authorization

```php
// Middleware stack
'auth'         # Must be logged in
'role:admin'   # Must be admin
'role:teacher' # Must be teacher
'parent'       # Must be parent (custom middleware)
```

### 2. Rate Limiting

```php
// Protect expensive AI calls
'throttle:20,1'  # Single generation: 20/min
'throttle:5,1'   # Bulk generation: 5/min
```

### 3. Data Validation

- All forms have server-side validation
- Request classes for complex validation
- CSRF protection on all forms
- SQL injection prevention (Eloquent ORM)

### 4. Soft Deletes

- Students use soft deletes
- Data recovery possible
- Prevents accidental data loss

---

## 📝 Configuration

### Environment Variables

```env
# Application
APP_NAME=PAUD-App
APP_ENV=local|production
APP_DEBUG=true|false
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paud_db
DB_USERNAME=root
DB_PASSWORD=

# Gemini AI ⭐
GEMINI_API_KEY=your_gemini_api_key_here

# Mail (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

### Config Files

**`config/services.php`**:

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

---

## 🚀 Deployment Checklist

- ✅ Run migrations: `php artisan migrate`
- ✅ Seed database: `php artisan db:seed`
- ✅ Set `GEMINI_API_KEY` in `.env`
- ✅ Set `APP_ENV=production`
- ✅ Set `APP_DEBUG=false`
- ✅ Configure mail settings
- ✅ Set proper permissions on `storage/` and `bootstrap/cache/`
- ✅ Run `php artisan optimize`
- ✅ Run `npm run build`

---

## 📞 Default Login Credentials (After Seeding)

### 👨‍💼 Admin:
```
Email: admin@paud.test
Password: password
```

### 👨‍🏫 Teacher:
```
Email: siti@paud.test
Password: password
```

### 👪 Parent:
```
Email: ahmad@example.com
Password: password
```

---

## 🎯 Summary

| Metric | Value |
|--------|-------|
| **Total Routes** | ~60+ routes |
| **User Roles** | 3 (Admin, Teacher, Parent) |
| **Main Services** | 2 (AI Narrative, PDF Generation) |
| **Database Tables** | 14 core tables |
| **Assessment Aspects** | 6 categories |
| **Score Levels** | 4 (BB, MB, BSH, BSB) |
| **AI Provider** | Google Gemini Pro |
| **PDF Library** | DomPDF |

### Application Status: ✅ 100% Production Ready

---

**Generated with ❤️ for PAUD Education Management**
