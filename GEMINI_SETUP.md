# Setup Gemini AI untuk Generate Narasi Raport

## Fitur AI Narrative Generation

Sistem ini menggunakan Google Gemini AI untuk otomatis membuat narasi deskriptif perkembangan anak berdasarkan nilai dan kata kunci yang diinput guru.

## Cara Mendapatkan API Key

1. Kunjungi [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Login dengan akun Google Anda
3. Klik **"Get API Key"** atau **"Create API Key"**
4. Copy API key yang digenerate

## Konfigurasi

1. Buka file `.env` di root project
2. Tambahkan baris berikut (atau edit jika sudah ada):

```env
GEMINI_API_KEY=your_api_key_here
```

3. Ganti `your_api_key_here` dengan API key yang Anda dapatkan dari Google AI Studio

4. Restart server Laravel:
```bash
php artisan config:clear
php artisan serve
```

## Cara Menggunakan

### 1. Generate Narasi Per Aspek

1. Buka menu **Raport** → Pilih kelas dan semester
2. Klik **"Input Penilaian"** untuk siswa yang ingin dinilai
3. Untuk setiap aspek penilaian:
   - Pilih **Nilai** (BB/MB/BSH/BSB)
   - (Opsional) Masukkan **Kata Kunci** yang menggambarkan perkembangan anak
   - Klik tombol **"Generate Narasi AI"**
4. AI akan otomatis membuat narasi deskriptif berdasarkan nilai dan kata kunci
5. Anda dapat mengedit narasi secara manual jika diperlukan
6. Klik **"Simpan Draft"** untuk menyimpan penilaian

### 2. Generate Semua Narasi Sekaligus

1. Lengkapi **semua nilai** untuk setiap aspek penilaian
2. (Opsional) Tambahkan kata kunci untuk setiap aspek
3. Klik tombol **"Generate Semua Narasi"** di bagian bawah
4. Tunggu beberapa detik, AI akan generate narasi untuk semua aspek
5. Review dan edit narasi jika diperlukan
6. Klik **"Simpan Draft"**

## Contoh Penggunaan

### Input:
- **Aspek**: Nilai Agama dan Moral
- **Nilai**: BSB (Berkembang Sangat Baik)
- **Kata Kunci**: "rajin berdoa, sopan, berbagi dengan teman"

### Output AI:
> "Ahmad menunjukkan perkembangan yang sangat baik dalam aspek Nilai Agama dan Moral. Ahmad rajin berdoa sebelum dan sesudah kegiatan, bersikap sopan kepada guru dan teman-teman, serta senang berbagi mainan dan makanan dengan teman. Ahmad juga mulai memahami pentingnya nilai-nilai kebaikan dalam kehidupan sehari-hari."

## Troubleshooting

### Tombol "Generate Narasi AI" Disabled
- Pastikan Anda sudah memilih **Nilai** untuk aspek tersebut
- Tombol hanya aktif setelah nilai dipilih

### Error "Gagal generate narasi"
- Pastikan `GEMINI_API_KEY` sudah dikonfigurasi di file `.env`
- Pastikan API key valid dan tidak expired
- Check koneksi internet
- Lihat log error di `storage/logs/laravel.log`

### Narasi Tidak Sesuai Harapan
- Tambahkan **Kata Kunci** yang lebih spesifik dan deskriptif
- Anda dapat mengedit narasi secara manual setelah di-generate
- Kata kunci yang baik: "mandiri, percaya diri, aktif bertanya, gemar membaca"
- Kata kunci kurang spesifik: "baik, bagus"

## Tips Menggunakan Kata Kunci

### ✅ Kata Kunci yang Baik:
- Spesifik dan deskriptif
- Menggambarkan perilaku konkret
- Contoh: "mandiri memakai sepatu, percaya diri presentasi, aktif bertanya"

### ❌ Kata Kunci yang Kurang Efektif:
- Terlalu umum
- Tidak deskriptif
- Contoh: "baik, bagus, pintar"

## Keamanan API Key

⚠️ **PENTING**:
- Jangan commit file `.env` ke Git/GitHub
- File `.env` sudah ada di `.gitignore` by default
- Jangan share API key Anda ke orang lain
- Jika API key ter-expose, segera regenerate di Google AI Studio

## Batasan & Quota

- Gemini API memiliki quota gratis per hari
- Jika quota habis, tunggu sampai reset (24 jam) atau upgrade ke paid plan
- Quota gratis: 60 requests per minute
- Untuk produksi dengan banyak guru, pertimbangkan upgrade ke paid plan

## Support

Jika ada masalah atau pertanyaan, hubungi developer atau buat issue di repository project.
