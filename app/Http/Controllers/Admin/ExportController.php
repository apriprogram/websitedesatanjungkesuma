<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf as PdfWriterMpdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class ExportController extends Controller
{
    public function exportUsers(Request $request)
    {
        $format = $request->input('format', 'pdf');
        // Select fields: id, nama, email, nomor_hp, is_admin, is_active, last_login
        $users = User::select(
            'id',
            'nama',
            'email',
            'nomor_hp',
            'is_admin',
            'is_active',
            'last_login'
        )->get();

        switch ($format) {
            case 'pdf':
                return $this->exportUsersPdf($users);
            case 'excel':
                return $this->exportUsersExcel($users);
            case 'word':
                return $this->exportUsersWord($users);
            default:
                return response()->json(['error' => 'Format tidak valid'], 400);
        }
    }

    public function exportPegawai(Request $request)
    {
        $format = $request->input('format', 'pdf');
        // Select all the requested pegawai fields. Note: model uses 'alamat' for address.
        $pegawai = Pegawai::select(
            'nama',
            'nik',
            'nip',
            'jabatan',
            'email',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama',
            'alamat',
            'nomor_hp',
            'status',
            'gambar',
            'foto_ktp',
            'sk_pengangkatan',
            'sk_pemberhentian',
            'masa_jabatan_mulai',
            'masa_jabatan_selesai',
            'universitas',
            'pendidikan_terakhir',
            'tahun_lulus',
            'sertifikat_pelatihan',
            'bahasa'
        )->get();

        switch ($format) {
            case 'pdf':
                return $this->exportPegawaiPdf($pegawai);
            case 'excel':
                return $this->exportPegawaiExcel($pegawai);
            case 'word':
                return $this->exportPegawaiWord($pegawai);
            default:
                return response()->json(['error' => 'Format tidak valid'], 400);
        }
    }

    private function exportUsersPdf($users)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        /** @var Worksheet $sheet */
        // Set headers
        $headers = ['No', 'Nama', 'Email', 'Nomor HP', 'Role', 'Status', 'Login Terakhir'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . '1', $header);
        }

        // Set data
        foreach ($users as $row => $user) {
            $rowNum = $row + 2;
            $sheet->setCellValue('A' . $rowNum, $row + 1); // No
            $sheet->setCellValue('B' . $rowNum, $user->nama ?? 'Data tidak ada');
            $sheet->setCellValue('C' . $rowNum, $user->email ?? 'Data tidak ada');
            $sheet->setCellValue('D' . $rowNum, $user->nomor_hp ?? 'Data tidak ada');
            $sheet->setCellValue('E' . $rowNum, $user->is_admin ? 'Super Admin' : 'Admin');
            $sheet->setCellValue('F' . $rowNum, $user->is_active ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('G' . $rowNum, $user->last_login ? $user->last_login->format('d/m/Y H:i') : 'Belum pernah');
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create PDF using Mpdf writer
        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf');

        // Save to temp file and return
        $tempFile = tempnam(sys_get_temp_dir(), 'users_export');
        $writer->save($tempFile);

        return response()->download($tempFile, 'data_admin_' . date('Y-m-d') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend();
    }

    private function exportUsersExcel($users)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        /** @var Worksheet $sheet */
        // Set headers
        $headers = ['No', 'Nama', 'Email', 'Nomor HP', 'Role', 'Status', 'Login Terakhir'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . '1', $header);
        }

        // Set data
        foreach ($users as $row => $user) {
            $rowNum = $row + 2;
            $sheet->setCellValue('A' . $rowNum, $row + 1); // No
            $sheet->setCellValue('B' . $rowNum, $user->nama ?? 'Data tidak ada');
            $sheet->setCellValue('C' . $rowNum, $user->email ?? 'Data tidak ada');
            $sheet->setCellValue('D' . $rowNum, $user->nomor_hp ?? 'Data tidak ada');
            $sheet->setCellValue('E' . $rowNum, $user->is_admin ? 'Super Admin' : 'Admin');
            $sheet->setCellValue('F' . $rowNum, $user->is_active ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('G' . $rowNum, $user->last_login ? $user->last_login->format('d/m/Y H:i') : 'Belum pernah');
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);

        // Save to temp file and return
        $tempFile = tempnam(sys_get_temp_dir(), 'users_export');
        $writer->save($tempFile);

        return response()->download($tempFile, 'data_admin_' . date('Y-m-d') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    private function exportUsersWord($users)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add title
        $section->addText('Data Admin Portal', ['bold' => true, 'size' => 16]);
        $section->addTextBreak();

        // Add table
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000']);

        // Add headers
        $table->addRow();
        $headers = ['No', 'Nama', 'Email', 'Nomor HP', 'Role', 'Status', 'Login Terakhir'];
        foreach ($headers as $header) {
            $table->addCell(2000)->addText($header, ['bold' => true]);
        }

        // Add data
        $no = 1;
        foreach ($users as $user) {
            $table->addRow();
            $table->addCell(2000)->addText((string) $no++);
            $table->addCell(2000)->addText($user->nama ?? 'Data tidak ada');
            $table->addCell(2000)->addText($user->email ?? 'Data tidak ada');
            $table->addCell(2000)->addText($user->nomor_hp ?? 'Data tidak ada');
            $table->addCell(2000)->addText($user->is_admin ? 'Super Admin' : 'Admin');
            $table->addCell(2000)->addText($user->is_active ? 'Aktif' : 'Nonaktif');
            $table->addCell(2000)->addText($user->last_login ? $user->last_login->format('d/m/Y H:i') : 'Belum pernah');
        }

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'users_export');
        $objWriter = WordIOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, 'data_admin_' . date('Y-m-d') . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    private function exportPegawaiPdf($pegawai)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        /** @var Worksheet $sheet */
        // Set headers
        $headers = [
            'Nama',
            'NIK',
            'Tempat Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Alamat',
            'Status',
            'Email',
            'Kontak',
            'Jabatan',
            'NIP',
            'Status Kepegawaian',
            'Masa Jabatan',
            'SK Pengangkatan',
            'SK Pemberhentian',
            'Universitas',
            'Pendidikan Terakhir',
            'Tahun Lulus',
            'Sertifikat Pelatihan/Kursus',
            'Bahasa'
        ];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . '1', $header);
        }

        // Set data
        foreach ($pegawai as $row => $p) {
            $rowNum = $row + 2;
            $tempatTanggalLahir = trim(($p->tempat_lahir ?? '') . ', ' . ($p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : ''));
            $tempatTanggalLahir = $tempatTanggalLahir === ', ' ? 'Data tidak ada' : $tempatTanggalLahir;

            $masaJabatan = '';
            if ($p->masa_jabatan_mulai && $p->masa_jabatan_selesai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d ' . $p->masa_jabatan_selesai->format('d/m/Y');
            } elseif ($p->masa_jabatan_mulai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d sekarang';
            } else {
                $masaJabatan = 'Data tidak ada';
            }

            $sheet->setCellValue('A' . $rowNum, $p->nama ?? 'Data tidak ada');
            $sheet->setCellValue('B' . $rowNum, $p->nik ?? 'Data tidak ada');
            $sheet->setCellValue('C' . $rowNum, $tempatTanggalLahir);
            $sheet->setCellValue('D' . $rowNum, $p->jenis_kelamin ?? 'Data tidak ada');
            $sheet->setCellValue('E' . $rowNum, $p->agama ?? 'Data tidak ada');
            $sheet->setCellValue('F' . $rowNum, $p->alamat ?? 'Data tidak ada');
            $sheet->setCellValue('G' . $rowNum, $p->status ?? 'Data tidak ada');
            $sheet->setCellValue('H' . $rowNum, $p->email ?? 'Data tidak ada');
            $sheet->setCellValue('I' . $rowNum, $p->nomor_hp ?? 'Data tidak ada');
            $sheet->setCellValue('J' . $rowNum, $p->jabatan ?? 'Data tidak ada');
            $sheet->setCellValue('K' . $rowNum, $p->nip ?? 'Data tidak ada');
            $sheet->setCellValue('L' . $rowNum, $p->status ?? 'Data tidak ada');
            $sheet->setCellValue('M' . $rowNum, $masaJabatan);
            $sheet->setCellValue('N' . $rowNum, $p->sk_pengangkatan ? 'Ada' : 'Tidak');
            $sheet->setCellValue('O' . $rowNum, $p->sk_pemberhentian ? 'Ada' : 'Tidak');
            $sheet->setCellValue('P' . $rowNum, $p->universitas ?? 'Data tidak ada');
            $sheet->setCellValue('Q' . $rowNum, $p->pendidikan_terakhir ?? 'Data tidak ada');
            $sheet->setCellValue('R' . $rowNum, $p->tahun_lulus ?? 'Data tidak ada');
            $sheet->setCellValue('S' . $rowNum, $p->sertifikat_pelatihan ?? 'Data tidak ada');
            $sheet->setCellValue('T' . $rowNum, $p->bahasa ?? 'Data tidak ada');
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create PDF using Mpdf writer
        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf');

        // Save to temp file and return
        $tempFile = tempnam(sys_get_temp_dir(), 'pegawai_export');
        $writer->save($tempFile);

        return response()->download($tempFile, 'data_pegawai_' . date('Y-m-d') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend();
    }

    private function exportPegawaiExcel($pegawai)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        /** @var Worksheet $sheet */

        // Set headers
        $headers = [
            'Nama',
            'NIK',
            'Tempat Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Alamat',
            'Status',
            'Email',
            'Kontak',
            'Jabatan',
            'NIP',
            'Status Kepegawaian',
            'Masa Jabatan',
            'SK Pengangkatan',
            'SK Pemberhentian',
            'Universitas',
            'Pendidikan Terakhir',
            'Tahun Lulus',
            'Sertifikat Pelatihan/Kursus',
            'Bahasa'
        ];
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Set data
        foreach ($pegawai as $row => $p) {
            $rowNum = $row + 2;
            $tempatTanggalLahir = trim(($p->tempat_lahir ?? '') . ', ' . ($p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : ''));
            $tempatTanggalLahir = $tempatTanggalLahir === ', ' ? 'Data tidak ada' : $tempatTanggalLahir;

            $masaJabatan = '';
            if ($p->masa_jabatan_mulai && $p->masa_jabatan_selesai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d ' . $p->masa_jabatan_selesai->format('d/m/Y');
            } elseif ($p->masa_jabatan_mulai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d sekarang';
            } else {
                $masaJabatan = 'Data tidak ada';
            }

            $sheet->setCellValue('A' . $rowNum, $p->nama ?? 'Data tidak ada');
            $sheet->setCellValue('B' . $rowNum, $p->nik ?? 'Data tidak ada');
            $sheet->setCellValue('C' . $rowNum, $tempatTanggalLahir);
            $sheet->setCellValue('D' . $rowNum, $p->jenis_kelamin ?? 'Data tidak ada');
            $sheet->setCellValue('E' . $rowNum, $p->agama ?? 'Data tidak ada');
            $sheet->setCellValue('F' . $rowNum, $p->alamat ?? 'Data tidak ada');
            $sheet->setCellValue('G' . $rowNum, $p->status ?? 'Data tidak ada');
            $sheet->setCellValue('H' . $rowNum, $p->email ?? 'Data tidak ada');
            $sheet->setCellValue('I' . $rowNum, $p->nomor_hp ?? 'Data tidak ada');
            $sheet->setCellValue('J' . $rowNum, $p->jabatan ?? 'Data tidak ada');
            $sheet->setCellValue('K' . $rowNum, $p->nip ?? 'Data tidak ada');
            $sheet->setCellValue('L' . $rowNum, $p->status ?? 'Data tidak ada');
            $sheet->setCellValue('M' . $rowNum, $masaJabatan);
            $sheet->setCellValue('N' . $rowNum, $p->sk_pengangkatan ? 'Ada' : 'Tidak');
            $sheet->setCellValue('O' . $rowNum, $p->sk_pemberhentian ? 'Ada' : 'Tidak');
            $sheet->setCellValue('P' . $rowNum, $p->universitas ?? 'Data tidak ada');
            $sheet->setCellValue('Q' . $rowNum, $p->pendidikan_terakhir ?? 'Data tidak ada');
            $sheet->setCellValue('R' . $rowNum, $p->tahun_lulus ?? 'Data tidak ada');
            $sheet->setCellValue('S' . $rowNum, $p->sertifikat_pelatihan ?? 'Data tidak ada');
            $sheet->setCellValue('T' . $rowNum, $p->bahasa ?? 'Data tidak ada');
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);

        // Save to temp file and return
        $tempFile = tempnam(sys_get_temp_dir(), 'pegawai_export');
        $writer->save($tempFile);

        return response()->download($tempFile, 'data_pegawai_' . date('Y-m-d') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    private function exportPegawaiWord($pegawai)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add title
        $section->addText('Data Pegawai', ['bold' => true, 'size' => 16]);
        $section->addTextBreak();

        // Add table
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000']);
        // Headers
        $headers = [
            'Nama',
            'NIK',
            'Tempat Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Alamat',
            'Status',
            'Email',
            'Kontak',
            'Jabatan',
            'NIP',
            'Status Kepegawaian',
            'Masa Jabatan',
            'SK Pengangkatan',
            'SK Pemberhentian',
            'Universitas',
            'Pendidikan Terakhir',
            'Tahun Lulus',
            'Sertifikat Pelatihan/Kursus',
            'Bahasa'
        ];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(1500)->addText($header, ['bold' => true]);
        }

        // Data rows
        foreach ($pegawai as $p) {
            $tempatTanggalLahir = trim(($p->tempat_lahir ?? '') . ', ' . ($p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : ''));
            $tempatTanggalLahir = $tempatTanggalLahir === ', ' ? 'Data tidak ada' : $tempatTanggalLahir;

            $masaJabatan = '';
            if ($p->masa_jabatan_mulai && $p->masa_jabatan_selesai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d ' . $p->masa_jabatan_selesai->format('d/m/Y');
            } elseif ($p->masa_jabatan_mulai) {
                $masaJabatan = $p->masa_jabatan_mulai->format('d/m/Y') . ' s/d sekarang';
            } else {
                $masaJabatan = 'Data tidak ada';
            }

            $table->addRow();
            $table->addCell(1500)->addText($p->nama ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->nik ?? 'Data tidak ada');
            $table->addCell(1500)->addText($tempatTanggalLahir);
            $table->addCell(1500)->addText($p->jenis_kelamin ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->agama ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->alamat ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->status ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->email ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->nomor_hp ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->jabatan ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->nip ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->status ?? 'Data tidak ada');
            $table->addCell(1500)->addText($masaJabatan);
            $table->addCell(1500)->addText($p->sk_pengangkatan ? 'Ada' : 'Tidak');
            $table->addCell(1500)->addText($p->sk_pemberhentian ? 'Ada' : 'Tidak');
            $table->addCell(1500)->addText($p->universitas ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->pendidikan_terakhir ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->tahun_lulus ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->sertifikat_pelatihan ?? 'Data tidak ada');
            $table->addCell(1500)->addText($p->bahasa ?? 'Data tidak ada');
        }

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'pegawai_export');
        $objWriter = WordIOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, 'data_pegawai_' . date('Y-m-d') . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }
}
