<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Kartu Keluarga - {{ $keluarga->no_kk }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 12pt;
        }
        .header p {
            margin: 0;
            font-size: 9pt;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h3 {
            margin: 0;
            text-decoration: underline;
            font-size: 13pt;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-grid td {
            vertical-align: top;
            padding: 3px 0;
        }
        .label {
            font-weight: bold;
            width: 150px;
        }
        .separator {
            width: 15px;
            text-align: center;
        }
        .member-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .member-table th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
            text-transform: uppercase;
        }
        .member-table td {
            border: 1px solid #ccc;
            padding: 5px;
            vertical-align: top;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer-sign {
            float: right;
            width: 250px;
            text-align: center;
        }
        .sign-space {
            height: 60px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-gray { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pemerintah Kabupaten Lampung Timur</h1>
        <h2>Kecamatan Purbolinggo - Desa Tanjung Kesuma</h2>
        <p>Alamat: Jl. Raya Tanjung Kesuma No. 01, Kode Pos 34192</p>
    </div>

    <div class="doc-title">
        <h3>LAPORAN DETAIL DATA KELUARGA</h3>
    </div>

    <table class="summary-grid">
        <tr>
            <td class="label">NOMOR KK</td>
            <td class="separator">:</td>
            <td><strong>{{ $keluarga->no_kk }}</strong></td>
            <td class="label">DUSUN</td>
            <td class="separator">:</td>
            <td>{{ $keluarga->dusun?->nama ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">KEPALA KELUARGA</td>
            <td class="separator">:</td>
            <td>{{ $kepala?->nama ?: '-' }}</td>
            <td class="label">RW / RT</td>
            <td class="separator">:</td>
            <td>{{ $keluarga->rw?->nomor ?: '-' }} / {{ $keluarga->rt?->nomor ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">ALAMAT</td>
            <td class="separator">:</td>
            <td colspan="4">{{ $keluarga->alamat ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">WHATSAPP / HP</td>
            <td class="separator">:</td>
            <td>{{ $kepala?->nomor_hp ?: '-' }}</td>
            <td class="label">EMAIL</td>
            <td class="separator">:</td>
            <td>{{ $kepala?->email ?: '-' }}</td>
        </tr>
    </table>

    <table class="member-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">Nama Lengkap / NIK</th>
                <th width="8%">Hubungan</th>
                <th width="4%">JK</th>
                <th width="12%">Tempat, Tgl Lahir</th>
                <th width="8%">Agama</th>
                <th width="10%">Pendidikan (KK / Sedang)</th>
                <th width="10%">Pekerjaan</th>
                <th width="8%">Status Kawin</th>
                <th width="10%">Kesehatan / Asuransi</th>
                <th width="12%">Alamat Saat Ini</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keluarga->penduduks as $member)
                <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray' : '' }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $member->nama }}</strong><br>
                        <span style="color: #666;">{{ $member->nik }}</span>
                    </td>
                    <td>{{ $member->kkLevel?->nama ?: '-' }}</td>
                    <td class="text-center">{{ $member->jenisKelamin?->nama == 'LAKI-LAKI' ? 'L' : 'P' }}</td>
                    <td>
                        {{ $member->tempat_lahir ?: '-' }},<br>
                        {{ $member->tanggal_lahir ? $member->tanggal_lahir->format('d-m-Y') : '-' }}
                    </td>
                    <td>{{ $member->agama?->nama ?: '-' }}</td>
                    <td>
                        KK: {{ $member->pendidikanKk?->nama ?: '-' }}<br>
                        SDN: {{ $member->pendidikanSedang?->nama ?: '-' }}
                    </td>
                    <td>{{ $member->pekerjaan?->nama ?: '-' }}</td>
                    <td>{{ $member->statusKawin?->nama ?: '-' }}</td>
                    <td>
                        Disab: {{ $member->cacat?->nama ?: '-' }}<br>
                        KB: {{ $member->caraKb?->nama ?: '-' }}<br>
                        {{ $member->id_asuransi ? $member->no_asuransi : 'Tanpa Asuransi' }}
                    </td>
                    <td>{{ $member->alamat_sekarang ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left; width: 300px; font-size: 8pt; color: #666;">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}<br>
            Oleh: Sistem Informasi Desa Tanjung Kesuma
        </div>
        <div class="footer-sign">
            Tanjung Kesuma, {{ now()->format('d F Y') }}<br>
            Kepala Desa Tanjung Kesuma
            <div class="sign-space"></div>
            <strong>( ............................................ )</strong>
        </div>
    </div>
</body>
</html>
