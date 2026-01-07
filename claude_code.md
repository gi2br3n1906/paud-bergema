# Project Context: Website Unit PAUD (PKBM Bergema)

## 1. Project Overview
**Name:** Unit PAUD - Sistem Informasi Akademik Cerdas
**Part of:** PKBM Bergema Ecosystem
**Purpose:** Sebuah platform manajemen sekolah PAUD yang mencakup profil publik, PPDB Online, manajemen akademik harian (presensi, mengaji, ibadah), berita acara kelas, pemantauan tumbuh kembang (stunting detection), dan pembuatan rapor naratif berbasis AI yang siap cetak.

## 2. Technical Stack & Environment
* **Backend:** Laravel 11 (PHP) running on Docker.
* **Frontend:** Vue.js 3 (Composition API + `<script setup>`) via Inertia.js.
    * *Styling:* TailwindCSS v3 + `@tailwindcss/forms` plugin.
    * *Icons:* Lucide-vue or Heroicons.
* **Database:** MySQL/MariaDB.
* **PDF Engine:** `spatie/browsershot` or `barryvdh/laravel-dompdf` for high-fidelity print layouts.
* **Infrastructure:**
    * Runs on `localhost:8001` inside a container.
    * Designed as a self-contained service (Microservice-ready).
* **AI Integration:** Google Gemini API (for Generative Report Cards).

## 3. Design Guidelines (UI/UX)
* **Vibe:** Ceria, Playful, Ramah Anak.
* **Color Palette:** Warna-warni Pastel (Kuning, Orange, Soft Blue) dengan kontras yang cukup untuk keterbacaan.
* **Typography:** Font yang agak bulat (Rounded) dan mudah dibaca (e.g., Nunito, Quicksand).
* **Mobile Experience:** Prioritas utama. Semua fitur Guru (Input Harian) dan Wali Murid (Pantau Anak) harus *Mobile-First*.

## 4. User Roles & Access Control
### Administrator (Kepala Sekolah)
* Full access to Master Data.
* **Import Data:** Ability to bulk import Students/Parents from **Dapodik Excel** files.
* **Verification:** Approve PPDB registrations and verify payments.
* Monitor Teacher performance & Class Journals.

### Guru Kelas (Operational)
* Input **Student Daily Logs** (Presensi, Ibadah, Mengaji).
* Input **Class Daily Journal** (Berita Acara Harian).
* Input Growth Data (Height/Weight).
* Generate AI Report Cards.

### Wali Murid (Parents)
* **Login Credential Strategy (Crucial):**
    * **Username:** Nomor HP (WhatsApp Number).
    * **Password:** Tanggal Lahir Anak (Format: `DDMMYYYY`).
    * *Note:* No email registration required. Accounts are auto-created via Dapodik Import or PPDB approval.
* **Access:**
    * Read-only access to Timeline, Growth Charts.
    * View Class Daily Journal.
    * Download "Ready-to-Print" PDF Report Cards.

### Guest (Public)
* View Public Profile, Teacher Directory.
* Access **PPDB Online** (Registration Stepper).

## 5. Functional Requirements (Detailed SRS)

### A. Public Module & PPDB
* **Home & Profile:** Cheerful school profile, vision/mission, facilities slider.
* **PPDB Online (New Feature):**
    * **Route:** `/ppdb`
    * **UI Pattern:** Multi-step Form (Stepper).
        1.  **Step 1:** Data Anak (Nama, NIK, Tgl Lahir).
        2.  **Step 2:** Data Orang Tua (Nama, NIK, **No WA/HP**).
        3.  **Step 3:** Upload Dokumen (KK/Akte/Bukti Bayar).
        4.  **Step 4:** Konfirmasi.
    * **Payment Flow:** Manual Transfer -> Upload Proof -> Verify via WhatsApp Link to Admin -> Admin Approves in Dashboard -> Student Data Created.

### B. Academic Module (Teacher Panel)
* **Daily Log System (Monitoring Harian - Per Siswa):**
    * *Presensi:* Input Status (Hadir/Sakit/Izin) + Arrival Time.
    * *Mutaba'ah Ibadah:* Checklist format (e.g., Sholat Dhuha, Doa Harian).
    * *Jurnal Mengaji:* Input Iqro Level, Page, and Quality Note.
* **Class Daily Journal (Berita Acara - Per Kelas):**
    * **Input:** Date, Activity Summary (e.g., "Tema Alam Semesta: Mewarnai Gunung"), Class Attendance Stats (Auto-calculated/Editable), Photo Documentation.
    * **Output:** A timeline view for Parents to see "What did the class do today?".
* **Growth & Health Tracker:**
    * Input: BB (kg), TB (cm), Lingkar Kepala.
    * Logic: WHO 2006 Z-Score Calculation (Normal/Stunting).

### C. AI-Powered Report Card (Core Feature)
* **Workflow:**
    1.  Teacher inputs **Keywords** & **Predikat** per aspect.
    2.  Gemini AI generates narrative description.
    3.  Teacher reviews/edits.
* **PDF Output (Print Ready):**
    * Feature to export the final report as a neatly formatted PDF (A4).
    * Layout must match the official Dinas Education format (kop surat, signatures, tables).

### D. Parent Portal (Wali Murid)
* **Dashboard:** Switch profile (if parent has >1 child).
* **Timeline:** Combined feed of Student Daily Logs + Class Daily Journal.
* **Rapor:** Download PDF button (only available if status is 'Published').

## 6. Database Schema Strategy
*Use English for Table/Column names.*

1.  **`users`**: Extended with roles.
    * *Unique Constraint:* Phone Number (for Parents).
2.  **`students`**: Linked to `parents` (users) and `classrooms`.
3.  **`ppdb_registrations`**: Temporary table for stepper data before approval.
4.  **`student_daily_logs`**: JSON column for flexible daily data (Use Custom Casts).
5.  **`class_journals`** (New):
    * `id`, `classroom_id`, `teacher_id`, `date`, `activity_summary`, `photo_url`, `stats_json`.
6.  **`growth_records`**: Anthropometric data.
7.  **`report_cards`** & **`report_details`**: Stores grades & AI narratives.

## 7. Implementation Priorities
1.  **Phase 1:** Database Schema & Models (Done).
2.  **Phase 2:** Authentication (Phone/DOB Logic) & Admin Master Data (Including **Dapodik Import**).
3.  **Phase 3:** Teacher Tools (**Class Journal** & Daily Logs).
4.  **Phase 4:** **PPDB Online** (Stepper & Public Layout).
5.  **Phase 5:** AI Integration (Gemini Service).
6.  **Phase 6:** PDF Generation (Report Card Layout).

## 8. Coding Conventions (Strict)
* **Controllers**: Keep skinny. Use Services (e.g., `GeminiService`, `DapodikImportService`, `PdfGeneratorService`).
* **Frontend**: ALWAYS use Vue 3 `<script setup>`.
* **Styling**: TailwindCSS Utility-first.
* **Type Safety**: Use Typescript JSDoc or explicit PHP types.
* **Validation**: Use FormRequest classes for ALL writes.

## 9. Critical Business Rules & Logic
* **Login Logic:** Parent Username = `phone_number`, Password = `dob` (dmY format, e.g., 31012020).
* **Import Logic:** When importing Dapodik Excel, system must auto-check if Parent's Phone Number exists. If yes -> link student. If no -> create new User.
* **Media Storage:** Use local storage (symlinked) for dev, S3-compatible for prod.
* **Payment:** No payment gateway API. Uses "Manual Verification" model.