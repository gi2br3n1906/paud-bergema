<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DapodikImportService
{
    /**
     * Import students and parents from CSV file
     *
     * Expected CSV format:
     * NISN, Nama Lengkap, Nama Panggilan, Jenis Kelamin, Tanggal Lahir, Tempat Lahir, Alamat,
     * Nama Orang Tua/Wali, No HP Orang Tua, Hubungan (Ayah/Ibu/Wali)
     *
     * @param string $filePath
     * @return array ['success' => int, 'errors' => array, 'created_students' => array, 'created_parents' => array]
     */
    public function importFromCsv(string $filePath): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'created_students' => [],
            'created_parents' => [],
            'linked_students' => [],
        ];

        if (!file_exists($filePath)) {
            $results['errors'][] = 'File tidak ditemukan';
            return $results;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $results['errors'][] = 'Tidak dapat membuka file';
            return $results;
        }

        // Skip header row
        fgetcsv($handle, 0, ',', '"', '\\');
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rowNumber++;

            try {
                DB::beginTransaction();

                // Parse student data
                $nisn = trim($row[0] ?? '');
                $studentName = trim($row[1] ?? '');
                $nickname = trim($row[2] ?? '');
                $gender = trim($row[3] ?? '');
                $dateOfBirth = trim($row[4] ?? '');
                $placeOfBirth = trim($row[5] ?? '');
                $address = trim($row[6] ?? '');
                $parentName = trim($row[7] ?? '');
                $parentPhone = trim($row[8] ?? '');
                $relationship = trim($row[9] ?? 'Orang Tua');

                // Validate required fields
                if (empty($nisn) || empty($studentName) || empty($dateOfBirth)) {
                    throw new \Exception("Baris {$rowNumber}: NISN, Nama, dan Tanggal Lahir wajib diisi");
                }

                // Normalize gender
                $gender = $this->normalizeGender($gender);
                if (!$gender) {
                    throw new \Exception("Baris {$rowNumber}: Jenis kelamin tidak valid. Gunakan 'L' atau 'P'");
                }

                // Parse date of birth
                $dob = $this->parseDate($dateOfBirth);
                if (!$dob) {
                    throw new \Exception("Baris {$rowNumber}: Format tanggal lahir tidak valid. Gunakan DD/MM/YYYY atau YYYY-MM-DD");
                }

                // Check if student already exists
                $student = Student::where('nisn', $nisn)->first();

                if (!$student) {
                    // Create new student
                    $student = Student::create([
                        'nisn' => $nisn,
                        'name' => $studentName,
                        'nickname' => $nickname ?: null,
                        'gender' => $gender,
                        'date_of_birth' => $dob,
                        'place_of_birth' => $placeOfBirth ?: null,
                        'address' => $address ?: null,
                        'enrollment_date' => now(),
                        'status' => 'Aktif',
                    ]);

                    $results['created_students'][] = [
                        'nisn' => $nisn,
                        'name' => $studentName,
                    ];
                }

                // Process parent if phone number is provided
                if (!empty($parentPhone) && !empty($parentName)) {
                    $parent = $this->createOrFindParent($parentPhone, $parentName, $dob);

                    if ($parent->wasRecentlyCreated) {
                        $results['created_parents'][] = [
                            'name' => $parentName,
                            'phone' => $parentPhone,
                        ];
                    }

                    // Normalize relationship type
                    $normalizedRelationship = $this->normalizeRelationship($relationship);

                    // Link parent to student if not already linked
                    if (!$student->parents()->where('user_id', $parent->id)->exists()) {
                        $student->parents()->attach($parent->id, [
                            'relationship_type' => $normalizedRelationship,
                            'is_primary_contact' => true,
                        ]);

                        $results['linked_students'][] = [
                            'student' => $studentName,
                            'parent' => $parentName,
                        ];
                    }
                }

                DB::commit();
                $results['success']++;

            } catch (\Exception $e) {
                DB::rollBack();
                $results['errors'][] = $e->getMessage();
                Log::error('Dapodik Import Error', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ]);
            }
        }

        fclose($handle);

        return $results;
    }

    /**
     * Create or find parent user by phone number
     * Password is set to date of birth in DDMMYYYY format
     *
     * @param string $phone
     * @param string $name
     * @param string $childDob
     * @return User
     */
    protected function createOrFindParent(string $phone, string $name, string $childDob): User
    {
        // Clean phone number (remove spaces, dashes, etc.)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Check if parent already exists
        $parent = User::where('phone_number', $phone)->first();

        if (!$parent) {
            // Generate password from child's date of birth (DDMMYYYY format)
            $dobCarbon = Carbon::parse($childDob);
            $password = $dobCarbon->format('dmY');

            $parent = User::create([
                'name' => $name,
                'email' => null, // Parents don't need email
                'phone_number' => $phone,
                'password' => Hash::make($password),
                'role' => UserRole::PARENT,
                'is_active' => true,
            ]);
        }

        return $parent;
    }

    /**
     * Normalize gender input to Indonesian format
     *
     * @param string $gender
     * @return string|null
     */
    protected function normalizeGender(string $gender): ?string
    {
        $gender = strtolower(trim($gender));

        return match ($gender) {
            'l', 'laki-laki', 'laki', 'male', 'm' => 'Laki-laki',
            'p', 'perempuan', 'female', 'f' => 'Perempuan',
            default => null,
        };
    }

    /**
     * Normalize relationship type to database enum format
     *
     * @param string $relationship
     * @return string
     */
    protected function normalizeRelationship(string $relationship): string
    {
        $relationship = strtolower(trim($relationship));

        return match ($relationship) {
            'ayah', 'bapak', 'father', 'dad' => 'father',
            'ibu', 'mother', 'mom', 'mama' => 'mother',
            'wali', 'guardian' => 'guardian',
            default => 'guardian',
        };
    }

    /**
     * Parse date from various formats
     *
     * @param string $date
     * @return string|null
     */
    protected function parseDate(string $date): ?string
    {
        try {
            // Try DD/MM/YYYY format
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
                return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            }

            // Try YYYY-MM-DD format
            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date)) {
                return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
            }

            // Try DD-MM-YYYY format
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $date)) {
                return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
