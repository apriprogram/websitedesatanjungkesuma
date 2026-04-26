<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PegawaiDetailBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $jabatanDefaults = [
            'Kepala Desa' => ['status' => 'Aktif', 'pendidikan' => 'S1 Pemerintahan'],
            'Sekretaris Desa' => ['status' => 'Aktif', 'pendidikan' => 'D3 Administrasi'],
            'Kaur Umum' => ['status' => 'Aktif', 'pendidikan' => 'SMA'],
            'Kaur Keuangan' => ['status' => 'Aktif', 'pendidikan' => 'D3 Akuntansi'],
            'Kaur Perencanaan' => ['status' => 'Aktif', 'pendidikan' => 'S1 Perencanaan Wilayah'],
            'Operator' => ['status' => 'Aktif', 'pendidikan' => 'S1 Informatika'],
        ];

        $genders = ['Laki-laki', 'Perempuan'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $languages = ['Indonesia', 'Indonesia, Lampung', 'Indonesia, Inggris'];

        Pegawai::chunkById(50, function ($pegawais) use ($jabatanDefaults, $genders, $religions, $languages) {
            foreach ($pegawais as $pegawai) {
                $updates = [];

                if (!$pegawai->nik) {
                    $updates['nik'] = '1871234567' . str_pad((string) $pegawai->id, 6, '0', STR_PAD_LEFT);
                }

                if (!$pegawai->nip) {
                    $updates['nip'] = '19780615' . str_pad((string) $pegawai->id, 8, '0', STR_PAD_LEFT);
                }

                if (!$pegawai->tempat_lahir) {
                    $updates['tempat_lahir'] = 'Tanjung Kesuma';
                }

                if (!$pegawai->tanggal_lahir) {
                    $offset = ($pegawai->id % 20) + 20;
                    $updates['tanggal_lahir'] = Carbon::now()->subYears(30 + $offset % 10)->subDays($offset);
                }

                if (!$pegawai->jenis_kelamin) {
                    $updates['jenis_kelamin'] = $genders[$pegawai->id % count($genders)];
                }

                if (!$pegawai->agama) {
                    $updates['agama'] = $religions[$pegawai->id % count($religions)];
                }

                if (!$pegawai->alamat) {
                    $updates['alamat'] = 'Dusun ' . chr(65 + ($pegawai->id % 5)) . ', Desa Tanjung Kesuma';
                }

                if (!$pegawai->universitas) {
                    $updates['universitas'] = 'Universitas Lampung';
                }

                if (!$pegawai->pendidikan_terakhir) {
                    $default = $jabatanDefaults[$pegawai->jabatan ?? ''] ?? null;
                    $updates['pendidikan_terakhir'] = $default['pendidikan'] ?? 'SMA';
                }

                if (!$pegawai->tahun_lulus) {
                    $updates['tahun_lulus'] = (string) (2010 + ($pegawai->id % 10));
                }

                if (!$pegawai->sertifikat_pelatihan) {
                    $updates['sertifikat_pelatihan'] = 'Pelatihan Aparatur Desa ' . (2020 + ($pegawai->id % 4));
                }

                if (!$pegawai->bahasa) {
                    $updates['bahasa'] = $languages[$pegawai->id % count($languages)];
                }

                if (!$pegawai->status || $pegawai->status === '') {
                    $default = $jabatanDefaults[$pegawai->jabatan ?? ''] ?? null;
                    $updates['status'] = $default['status'] ?? 'Aktif';
                }

                if (!$pegawai->masa_jabatan_mulai) {
                    $updates['masa_jabatan_mulai'] = Carbon::now()->subYears(($pegawai->id % 5) + 1)->startOfYear();
                }

                if (!$pegawai->last_login) {
                    $updates['last_login'] = Carbon::now()->subMonths($pegawai->id % 6)->setTime(8, 0);
                }

                if (!$pegawai->universitas) {
                    $updates['universitas'] = 'Universitas Lampung';
                }

                if (!$pegawai->foto_ktp) {
                    $updates['foto_ktp'] = 'pegawai/ktp_' . Str::slug($pegawai->nama ?? 'pegawai') . '.jpg';
                }

                if (!$pegawai->sk_pengangkatan) {
                    $updates['sk_pengangkatan'] = 'dokumen/sk_pengangkatan_' . Str::slug($pegawai->nama ?? 'pegawai') . '.pdf';
                }

                if (!empty($updates)) {
                    $pegawai->forceFill($updates)->save();
                }
            }
        });
    }
}

