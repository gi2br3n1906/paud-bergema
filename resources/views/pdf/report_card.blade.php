<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport {{ $student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        .page {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }

        /* Header dengan Kop Surat */
        .header {
            text-align: center;
            border: 3px double #000;
            padding: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header .address {
            font-size: 11pt;
            line-height: 1.4;
        }

        /* Title Section */
        .title {
            text-align: center;
            margin: 20px 0;
        }

        .title h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }

        /* Student Info Table */
        .student-info {
            margin-bottom: 20px;
        }

        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 5px;
            vertical-align: top;
        }

        .student-info .label {
            width: 150px;
            font-weight: normal;
        }

        .student-info .colon {
            width: 20px;
        }

        /* Grades Table */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .grades-table th,
        .grades-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .grades-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .grades-table .center {
            text-align: center;
        }

        .grades-table .number-col {
            width: 40px;
            text-align: center;
        }

        .grades-table .score-col {
            width: 80px;
            text-align: center;
        }

        /* Narrative Section */
        .narrative-section {
            margin-bottom: 30px;
        }

        .narrative-section h3 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .narrative-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 150px;
            text-align: justify;
            line-height: 1.8;
        }

        .narrative-box p {
            margin-bottom: 10px;
        }

        /* Signatures Section */
        .signatures {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures td {
            width: 33.33%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }

        .signature-box {
            margin-top: 80px;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 5px;
        }

        /* Notes Section */
        .notes {
            margin-top: 20px;
            font-size: 10pt;
        }

        .notes h4 {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notes ul {
            list-style-type: none;
            padding-left: 0;
        }

        .notes li {
            margin-bottom: 3px;
        }

        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                page-break-after: always;
            }

            .signatures {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header / Kop Surat -->
        <div class="header">
            <h1>PAUD BERGEMA</h1>
            <div class="subtitle">Pendidikan Anak Usia Dini</div>
            <div class="address">
                Jl. Pendidikan No. 123, Kota, Provinsi<br>
                Telp: (021) 12345678 | Email: info@paudbergema.sch.id
            </div>
        </div>

        <!-- Title -->
        <div class="title">
            <h2>LAPORAN PERKEMBANGAN ANAK DIDIK</h2>
            <p>TAHUN AJARAN {{ $academicTerm->academic_year->year }}</p>
            <p>SEMESTER {{ strtoupper($academicTerm->semester) }}</p>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <table>
                <tr>
                    <td class="label">Nama Peserta Didik</td>
                    <td class="colon">:</td>
                    <td><strong>{{ $student->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">NISN</td>
                    <td class="colon">:</td>
                    <td>{{ $student->nisn ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kelompok</td>
                    <td class="colon">:</td>
                    <td>{{ $student->classroom->name }}</td>
                </tr>
                <tr>
                    <td class="label">Semester</td>
                    <td class="colon">:</td>
                    <td>{{ ucfirst($academicTerm->semester) }} - {{ $academicTerm->academic_year->year }}</td>
                </tr>
            </table>
        </div>

        <!-- Grades Table -->
        <table class="grades-table">
            <thead>
                <tr>
                    <th class="number-col">No</th>
                    <th>Aspek Perkembangan</th>
                    <th class="score-col">Capaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportDetails as $index => $detail)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $detail->assessmentAspect->name }}</td>
                    <td class="center"><strong>{{ $detail->score }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="center" style="font-style: italic; color: #666;">
                        Belum ada penilaian
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Legend -->
        <div class="notes">
            <h4>Keterangan Capaian Perkembangan:</h4>
            <ul>
                <li><strong>BB</strong> = Belum Berkembang (anak belum menunjukkan kemampuan sesuai indikator)</li>
                <li><strong>MB</strong> = Mulai Berkembang (anak mulai menunjukkan kemampuan dengan bantuan)</li>
                <li><strong>BSH</strong> = Berkembang Sesuai Harapan (anak menunjukkan kemampuan secara mandiri)</li>
                <li><strong>BSB</strong> = Berkembang Sangat Baik (anak menunjukkan kemampuan melebihi harapan)</li>
            </ul>
        </div>

        <!-- Narrative Section -->
        <div class="narrative-section">
            <h3>Catatan Perkembangan:</h3>
            <div class="narrative-box">
                @if($reportDetails->isNotEmpty())
                    @foreach($reportDetails as $detail)
                        @if($detail->narrative)
                            <p><strong>{{ $detail->assessmentAspect->name }}:</strong> {{ $detail->narrative }}</p>
                        @endif
                    @endforeach
                @else
                    <p style="font-style: italic; color: #666;">
                        Catatan perkembangan akan diisi oleh guru kelas.
                    </p>
                @endif
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <table>
                <tr>
                    <td>
                        <div>Orang Tua/Wali</div>
                        <div class="signature-box"></div>
                        <div class="signature-name">
                            {{ $student->parent_name ?? '(......................)' }}
                        </div>
                    </td>
                    <td>
                        <div>Guru Kelas</div>
                        <div class="signature-box"></div>
                        <div class="signature-name">
                            {{ $reportCard->creator->name ?? '(......................)' }}
                        </div>
                    </td>
                    <td>
                        <div>Kepala Sekolah</div>
                        <div class="signature-box"></div>
                        <div class="signature-name">
                            (......................)
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Note -->
        <div style="margin-top: 30px; text-align: center; font-size: 10pt; font-style: italic;">
            Dicetak pada: {{ now()->format('d F Y') }}
        </div>
    </div>
</body>
</html>
