<?php

namespace Database\Seeders;

use App\Models\Keluarga;
use App\Models\Penduduk;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RandomPendudukSeeder extends Seeder
{
    private array $usedNik = [];
    private array $usedKk = [];

    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now = now();

        DB::transaction(function () use ($faker, $now) {
            $dusunId = DB::table('dusuns')->value('id');
            if (! $dusunId) {
                $dusunId = DB::table('dusuns')->insertGetId([
                    'nama' => 'Dusun Utama',
                    'kode' => 'DSN-001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $rwId = DB::table('rws')->value('id');
            if (! $rwId) {
                $rwId = DB::table('rws')->insertGetId([
                    'dusun_id' => $dusunId,
                    'nomor' => '001',
                    'kode' => 'RW-001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $rtId = DB::table('rts')->value('id');
            if (! $rtId) {
                $rtId = DB::table('rts')->insertGetId([
                    'rw_id' => $rwId,
                    'nomor' => '001',
                    'kode' => 'RT-001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $jkMap = $this->ensureReferenceWithNames('ref_jenis_kelamin', ['LAKI-LAKI', 'PEREMPUAN']);
            $jkIds = array_keys($jkMap);
            $maleId = $this->findIdByName($jkMap, 'LAKI');
            $femaleId = $this->findIdByName($jkMap, 'PEREM');

            $agamaIds = $this->ensureReference('ref_agama', ['Islam']);
            $pendidikanIds = $this->ensureReference('ref_pendidikan', ['SD']);
            $pekerjaanIds = $this->ensureReference('ref_pekerjaan', ['Petani']);
            $statusKawinIds = $this->ensureReference('ref_status_kawin', ['Belum Kawin']);
            $hubunganIds = $this->ensureReference('ref_hub_keluarga', ['Kepala Keluarga']);
            $warganegaraIds = $this->ensureReference('ref_warganegara', ['WNI']);
            $golonganIds = $this->ensureReference('ref_golongan_darah', ['O']);
            $statusRekamIds = $this->ensureReference('ref_status_rekam', ['Terekam']);
            $sukuIds = $this->ensureReference('ref_suku', ['Suku Lokal']);

            $hidupId = DB::table('ref_status_dasar')
                ->whereRaw('UPPER(nama) = ?', ['HIDUP'])
                ->value('id')
                ?? DB::table('ref_status_dasar')->insertGetId([
                    'nama' => 'HIDUP',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $kkHeadId = DB::table('ref_hub_keluarga')
                ->whereRaw('UPPER(nama) = ?', ['KEPALA KELUARGA'])
                ->value('id') ?? ($hubunganIds[0] ?? null);

            for ($i = 0; $i < 50; $i++) {
                $genderId = $faker->randomElement($jkIds);
                $name = $faker->name();
                if ($genderId === $maleId) {
                    $name = $faker->name('male');
                } elseif ($genderId === $femaleId) {
                    $name = $faker->name('female');
                }

                $dob = $faker->dateTimeBetween('-65 years', '-10 years')->format('Y-m-d');
                $nik = $this->uniqueNik();
                $noKk = $this->uniqueKk();

                $keluarga = Keluarga::create([
                    'no_kk' => $noKk,
                    'kepala_nik' => $nik,
                    'alamat' => $faker->streetAddress(),
                    'dusun_id' => $dusunId,
                    'rw_id' => $rwId,
                    'rt_id' => $rtId,
                ]);

                Penduduk::create([
                    'no_kk' => $keluarga->no_kk,
                    'nik' => $nik,
                    'nama' => $name,
                    'jenis_kelamin_id' => $genderId,
                    'tempat_lahir' => $faker->city(),
                    'tanggal_lahir' => $dob,
                    'agama_id' => $faker->randomElement($agamaIds),
                    'pendidikan_kk_id' => $faker->randomElement($pendidikanIds),
                    'pendidikan_sedang_id' => $faker->randomElement($pendidikanIds),
                    'pekerjaan_id' => $faker->randomElement($pekerjaanIds),
                    'status_kawin_id' => $faker->randomElement($statusKawinIds),
                    'kk_level_id' => $kkHeadId ?: $faker->randomElement($hubunganIds),
                    'warganegara_id' => $faker->randomElement($warganegaraIds),
                    'ayah_nik' => null,
                    'nama_ayah' => $faker->optional(0.6)->name('male'),
                    'ibu_nik' => null,
                    'nama_ibu' => $faker->optional(0.6)->name('female'),
                    'golongan_darah_id' => $faker->optional()->randomElement($golonganIds),
                    'akta_lahir' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'cacat_id' => null,
                    'cara_kb_id' => null,
                    'hamil' => null,
                    'ktp_el' => $faker->boolean(80),
                    'status_rekam_id' => $faker->optional()->randomElement($statusRekamIds),
                    'alamat' => $keluarga->alamat,
                    'alamat_sekarang' => $faker->optional(0.3)->streetAddress(),
                    'dusun_id' => $dusunId,
                    'rw_id' => $rwId,
                    'rt_id' => $rtId,
                    'status_dasar_id' => $hidupId,
                    'suku_id' => $faker->optional(0.4)->randomElement($sukuIds),
                    'tag_id_card' => null,
                    'id_asuransi' => null,
                    'no_asuransi' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    private function ensureReference(string $table, array $fallbackNames = []): array
    {
        $ids = DB::table($table)->pluck('id')->all();
        if (! empty($ids)) {
            return $ids;
        }

        if (! empty($fallbackNames)) {
            $payload = collect($fallbackNames)->map(fn ($name) => [
                'nama' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            DB::table($table)->insert($payload);

            return DB::table($table)->pluck('id')->all();
        }

        return [];
    }

    private function ensureReferenceWithNames(string $table, array $names): array
    {
        $rows = DB::table($table)->get(['id', 'nama']);
        if ($rows->isEmpty() && ! empty($names)) {
            $payload = collect($names)->map(fn ($name) => [
                'nama' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            DB::table($table)->insert($payload);
            $rows = DB::table($table)->get(['id', 'nama']);
        }

        return $rows->mapWithKeys(fn ($row) => [(int) $row->id => (string) $row->nama])->all();
    }

    private function findIdByName(array $map, string $needle): ?int
    {
        $upperNeedle = strtoupper($needle);
        foreach ($map as $id => $name) {
            if (str_contains(strtoupper($name), $upperNeedle)) {
                return (int) $id;
            }
        }

        return null;
    }

    private function uniqueNik(): string
    {
        do {
            $nik = (string) random_int(1000000000000000, 9999999999999999);
        } while (in_array($nik, $this->usedNik, true) || DB::table('penduduks')->where('nik', $nik)->exists());

        $this->usedNik[] = $nik;

        return $nik;
    }

    private function uniqueKk(): string
    {
        do {
            $kk = (string) random_int(1000000000000000, 9999999999999999);
        } while (in_array($kk, $this->usedKk, true) || DB::table('keluargas')->where('no_kk', $kk)->exists());

        $this->usedKk[] = $kk;

        return $kk;
    }
}
