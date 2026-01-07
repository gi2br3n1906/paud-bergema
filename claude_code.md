Project Context: Website Unit PAUD (PKBM Bergema)
1. Project Overview
Name: Unit PAUD - Sistem Informasi Akademik Cerdas
Part of: PKBM Bergema Ecosystem
Purpose: Sebuah platform manajemen sekolah PAUD yang mencakup profil publik, manajemen akademik harian (presensi, mengaji, ibadah), pemantauan tumbuh kembang (stunting detection), dan pembuatan rapor naratif berbasis AI.

2. Technical Stack & Environment

Backend: Laravel 11 (PHP) running on Docker.

Frontend: Vue.js 3 (Composition API + `<script setup>`) via Inertia.js.
- Styling: TailwindCSS v3 + `@tailwindcss/forms` plugin.
- Icons: Lucide-vue or Heroicons.

Database: MySQL/MariaDB.

Infrastructure:

Runs on localhost:8001 inside a container.

Designed as a self-contained service (Microservice-ready) within the PKBM ecosystem.


AI Integration: Google Gemini API (for Generative Report Cards).

3. Design Guidelines (UI/UX)
Vibe: Ceria, Playful, Ramah Anak.


Color Palette: Warna-warni Pastel (Kuning, Orange, Soft Blue).


Typography: Font yang agak bulat (Rounded) dan mudah dibaca.


Mobile Experience: Prioritas tinggi untuk panel Guru (Input Harian) dan Wali Murid.

4. User Roles & Access Control 

Administrator (Kepala Sekolah):

Full access to Master Data (Classes, Academic Years, Dynamic Assessment Criteria).

Monitor Teacher performance & Student health stats.

Guru Kelas (Operational):

Input Daily Logs (Presensi, Ibadah, Mengaji).

Input Growth Data (Height/Weight).

Generate AI Report Cards.

Wali Murid (Parents):

Read-only access to their child's Timeline, Growth Charts, and PDF Reports.

Guest (Public):

View Public Profile, Teacher Directory, Registration Info.

5. Functional Requirements (Detailed SRS)
A. Public Module

Home & Profile: Show cheerful school profile, vision/mission, and facilities.


Guru Directory: Grid layout showing teacher photos, names, and competency/motto.


Info & PPDB: Information on registration costs/requirements and a clear "Login Wali Murid" button.

B. Academic Module (Teacher Panel)

Daily Log System (Monitoring Harian):

Presensi: Input Status (Hadir/Sakit/Izin) + Arrival Time.

Mutaba'ah Ibadah: Checklist format (e.g., Sholat Dhuha, Doa Harian, Hafalan Surat).

Jurnal Mengaji: Input Iqro Level (Jilid), Page (Halaman), and Quality Note.


UX Note: Must be tap-friendly (large buttons) for mobile use.


Growth & Health Tracker:

Input: Berat Badan (kg), Tinggi Badan (cm), Lingkar Kepala (cm).

Logic: Auto-calculate status (Normal/Stunting/Overweight) based on standard growth charts (visualized as Green/Red indicators).

C. AI-Powered Report Card (Core Feature) 

Workflow:

Teacher selects a student.

Teacher inputs Keywords describing behavior/progress (e.g., "mandiri", "mulai lancar membaca", "suka berbagi").

Teacher selects Predikat (Baik/Cukup/Perlu Bimbingan).

System Action: Send data to Gemini AI to generate a personalized narrative paragraph (Description).

Teacher reviews/edits the generated text before saving.


Configuration: Assessment aspects (Agama, Motorik, Kognitif) must be dynamic (configurable by Admin), not hardcoded.

Technical Note:
- Store Prompt Templates in database or structured config files to allow easy tuning.
- Implement Queue/Jobs for AI generation to prevent request timeouts.

D. Parent Portal (Wali Murid)

Timeline: Feed-style view (Instagram-like) showing today's activity, prayer checklists, and eating logs.

Rapor Digital:

View Growth Charts (Grafik Pertumbuhan).

Download Semester Report Card as PDF.

6. Database Schema Strategy (Guidance for Migration)
Use English for Table/Column names, but map them to the logic above.

users: Extended with roles (Admin, Guru, Wali).

students: Linked to parents (users) and classes. Data: Name, DOB, Photo.

student_daily_logs:

Type: Enum/String ('presence', 'worship', 'quran').

Data: JSON column to store flexible data.
- *Best Practice*: Use Laravel Custom Casts or DTOs to enforce structure (e.g., `WorshipDataCast`, `QuranProgressCast`).
- Example: `{"surah": "An-Nas", "status": "lancar"}` or `{"dhuha": true}`.

growth_records: student_id, date, height, weight, head_circumference, notes.

assessment_aspects: id, name (e.g., Kognitif), description.

report_cards: student_id, academic_term_id, narrative_result (AI Output), status (Draft/Published).

report_details: Scores per aspect.

7. Implementation Priorities
Phase 1: Database Schema & Models.

Phase 2: Authentication & Master Data (Admin Panel).

Phase 3: Teacher Daily Tools (The high-traffic mobile features).

Phase 4: AI Integration (Gemini Service).

Phase 5: Parent View & PDF Generation.

8. Coding Conventions (Strict)
- **Controllers**: Keep skinny. Move complex business logic to Services or Actions.
- **Frontend**: ALWAYS use Vue 3 `<script setup>`.
- **Styling**: TailwindCSS Utility-first. Avoid custom CSS files unless necessary.
- **Type Safety**: Use Typescript JSDoc or explicit PHP types strictly.
- **API Responses**: Use Laravel Eloquent Resources/API Resources to standardize responses.
- **Validation**: Use FormRequest classes for ALL writes (POST/PUT).

9. Critical Business Rules & Logic
- **Stunting Standard**: Use **WHO 2006** Growth Standards (Z-Score) for calculation logic.
- **Media Storage**:
    - **Dev**: Local `public` disk (symbolically linked).
    - **Prod**: S3-compatible storage (e.g., Cloudflare R2) is recommended.
- **User Onboarding**:
    - **Teachers/Staff**: Created by Admin manually.
    - **Parents**: Created by Admin, credentials distributed (No public registration).
- **Parent Access**: Supports **Many-to-Many** (A parent account can be linked to multiple students).