<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AcademicTerm;
use App\Models\AssessmentAspect;
use App\Models\PromptTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@paud.test',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        // 2. Create Sample Teachers
        User::create([
            'name' => 'Bu Siti Aminah',
            'email' => 'siti@paud.test',
            'password' => Hash::make('password'),
            'role' => UserRole::TEACHER,
            'phone_number' => '08123456789',
            'teacher_competency' => 'PAUD & Tahfidz',
            'teacher_motto' => 'Mendidik dengan cinta dan kesabaran',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Bu Fatimah',
            'email' => 'fatimah@paud.test',
            'password' => Hash::make('password'),
            'role' => UserRole::TEACHER,
            'phone_number' => '08198765432',
            'teacher_competency' => 'Pendidikan Karakter',
            'teacher_motto' => 'Setiap anak adalah bintang',
            'is_active' => true,
        ]);

        // 3. Create Sample Parents
        User::create([
            'name' => 'Bapak Ahmad',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::PARENT,
            'phone_number' => '08111222333',
            'is_active' => true,
        ]);

        // 4. Create Academic Year 2024/2025
        $academicYear = AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // 5. Create Academic Terms (Semesters)
        AcademicTerm::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Semester 1',
            'start_date' => '2024-07-15',
            'end_date' => '2024-12-20',
            'is_active' => true,
        ]);

        AcademicTerm::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Semester 2',
            'start_date' => '2025-01-07',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        // 5b. Create Sample Classrooms
        $teacher1 = User::where('email', 'siti@paud.test')->first();
        $teacher2 = User::where('email', 'fatimah@paud.test')->first();

        \App\Models\Classroom::create([
            'name' => 'Kelas A Pagi',
            'level' => 'KB (Kelompok Bermain)',
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher1->id,
            'capacity' => 15,
            'description' => 'Kelas untuk anak usia 2-3 tahun',
        ]);

        $classroom2 = \App\Models\Classroom::create([
            'name' => 'Kelas B Siang',
            'level' => 'TK A',
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher2->id,
            'capacity' => 20,
            'description' => 'Kelas untuk anak usia 4-5 tahun',
        ]);

        // 5c. Create Sample Students
        $parent = User::where('email', 'ahmad@example.com')->first();

        $student1 = \App\Models\Student::create([
            'nisn' => '0011223344',
            'name' => 'Ahmad Fadli',
            'gender' => 'Laki-laki',
            'date_of_birth' => '2020-03-15',
            'place_of_birth' => 'Jakarta',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
            'classroom_id' => $classroom2->id,
            'enrollment_date' => '2024-07-15',
            'status' => 'Aktif',
        ]);
        $student1->parents()->attach($parent->id, ['relationship_type' => 'father']);

        $student2 = \App\Models\Student::create([
            'nisn' => '0011223355',
            'name' => 'Siti Aisyah',
            'gender' => 'Perempuan',
            'date_of_birth' => '2020-05-20',
            'place_of_birth' => 'Jakarta',
            'address' => 'Jl. Melati No. 45, Jakarta',
            'classroom_id' => $classroom2->id,
            'enrollment_date' => '2024-07-15',
            'status' => 'Aktif',
        ]);
        $student2->parents()->attach($parent->id, ['relationship_type' => 'father']);

        $student3 = \App\Models\Student::create([
            'nisn' => '0011223366',
            'name' => 'Muhammad Rizki',
            'gender' => 'Laki-laki',
            'date_of_birth' => '2020-01-10',
            'place_of_birth' => 'Jakarta',
            'address' => 'Jl. Kenanga No. 78, Jakarta',
            'classroom_id' => $classroom2->id,
            'enrollment_date' => '2024-07-15',
            'status' => 'Aktif',
        ]);
        $student3->parents()->attach($parent->id, ['relationship_type' => 'father']);

        // 6. Create Assessment Aspects (6 Aspek Perkembangan PAUD)
        $aspects = [
            [
                'name' => 'Mengenal perilaku yang baik dan sopan',
                'category' => 'Nilai Agama dan Moral',
                'description' => 'Penilaian terhadap pemahaman nilai-nilai keagamaan dan moral'
            ],
            [
                'name' => 'Motorik kasar (berlari, melompat, melempar)',
                'category' => 'Fisik Motorik',
                'description' => 'Penilaian perkembangan motorik kasar'
            ],
            [
                'name' => 'Motorik halus (menulis, menggunting, mewarnai)',
                'category' => 'Fisik Motorik',
                'description' => 'Penilaian perkembangan motorik halus'
            ],
            [
                'name' => 'Mengenal konsep bilangan dan angka',
                'category' => 'Kognitif',
                'description' => 'Penilaian kemampuan berpikir logis matematis'
            ],
            [
                'name' => 'Mengenal warna, bentuk, dan ukuran',
                'category' => 'Kognitif',
                'description' => 'Penilaian kemampuan mengenal konsep dasar'
            ],
            [
                'name' => 'Berkomunikasi secara lisan',
                'category' => 'Bahasa',
                'description' => 'Penilaian kemampuan berbicara dan berkomunikasi'
            ],
            [
                'name' => 'Mengenal huruf dan membaca sederhana',
                'category' => 'Bahasa',
                'description' => 'Penilaian kemampuan keaksaraan awal'
            ],
            [
                'name' => 'Bermain dan bekerja sama dengan teman',
                'category' => 'Sosial Emosional',
                'description' => 'Penilaian kemampuan berinteraksi sosial'
            ],
            [
                'name' => 'Menyanyi dan mengekspresikan seni',
                'category' => 'Seni',
                'description' => 'Penilaian kreativitas dan apresiasi seni'
            ],
        ];

        foreach ($aspects as $aspect) {
            AssessmentAspect::create($aspect);
        }

        // 7. Create AI Prompt Templates for Report Cards
        PromptTemplate::create([
            'name' => 'report_card_general',
            'category' => 'report_card',
            'template' => 'Buatlah narasi rapor PAUD untuk aspek perkembangan "{aspect_name}".

Karakteristik dan pencapaian siswa: {keywords}
Tingkat pencapaian: {predicate}

Tulislah 1 paragraf naratif (100-150 kata) yang:
1. Hangat dan memotivasi
2. Spesifik terhadap karakteristik anak
3. Memberikan apresiasi atas pencapaian
4. Memberikan saran konstruktif untuk pengembangan
5. Menggunakan bahasa Indonesia yang baik dan mudah dipahami orang tua

Hindari kalimat yang terlalu formal atau kaku. Gunakan nada yang ramah dan penuh harapan.',
            'variables' => ['aspect_name', 'keywords', 'predicate'],
            'is_active' => true,
        ]);

        PromptTemplate::create([
            'name' => 'report_card_agama',
            'category' => 'report_card',
            'template' => 'Buatlah narasi rapor PAUD untuk aspek "Nilai Agama dan Moral".

Pencapaian anak: {keywords}
Tingkat pencapaian: {predicate}

Tulislah 1 paragraf naratif (100-150 kata) yang mencerminkan perkembangan nilai agama dan moral anak. Gunakan bahasa yang hangat, memotivasi, dan sesuai dengan konteks pendidikan Islam. Sertakan apresiasi dan saran pengembangan.',
            'variables' => ['keywords', 'predicate'],
            'is_active' => true,
        ]);

        PromptTemplate::create([
            'name' => 'report_card_kognitif',
            'category' => 'report_card',
            'template' => 'Buatlah narasi rapor PAUD untuk aspek "Kognitif" (kemampuan berpikir dan menalar).

Pencapaian anak: {keywords}
Tingkat pencapaian: {predicate}

Tulislah 1 paragraf naratif (100-150 kata) yang menggambarkan perkembangan kognitif anak dengan bahasa yang mudah dipahami orang tua. Fokus pada kemampuan berpikir logis, menyelesaikan masalah, dan rasa ingin tahu anak.',
            'variables' => ['keywords', 'predicate'],
            'is_active' => true,
        ]);

        // 8. Call AssessmentAspectSeeder for comprehensive aspects with ordering
        $this->call([
            AssessmentAspectSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('📧 Admin: admin@paud.test | Password: password');
        $this->command->info('👨‍🏫 Teacher: siti@paud.test | Password: password');
        $this->command->info('👪 Parent: ahmad@example.com | Password: password');
    }
}
