<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Penduduk;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Define Standard Data
        $standards = [
            'ref_agama' => [
                'ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KHONGHUCU', 'PENGHAYAT KEPERCAYAAN'
            ],
            'ref_pendidikan' => [
                'TIDAK / BELUM SEKOLAH',
                'BELUM TAMAT SD / SEDERAJAT',
                'TAMAT SD / SEDERAJAT',
                'SLTP / SEDERAJAT',
                'SLTA / SEDERAJAT',
                'DIPLOMA I / II',
                'AKADEMI / DIPLOMA III / S. MUDA',
                'DIPLOMA IV / STRATA I',
                'STRATA II',
                'STRATA III'
            ],
            'ref_golongan_darah' => [
                'A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'TIDAK TAHU'
            ],
            'ref_status_kawin' => [
                'BELUM KAWIN', 'KAWIN', 'CERAI HIDUP', 'CERAI MATI'
            ],
            'ref_hub_keluarga' => [
                'KEPALA KELUARGA', 'SUAMI', 'ISTRI', 'ANAK', 'MENANTU', 'CUCU', 'ORANG TUA', 'MERTUA', 'FAMILI LAIN', 'PEMBANTU', 'LAINNYA'
            ],
            'ref_jenis_kelamin' => [
                'LAKI-LAKI', 'PEREMPUAN'
            ],
            'ref_warganegara' => [
                'WNI', 'WNA'
            ],
            'ref_status_dasar' => [
                'HIDUP', 'MATI', 'PINDAH', 'PINDAH DATANG', 'HILANG'
            ],
            'ref_cacat' => [
                'TANPA CACAT', 'CACAT FISIK', 'CACAT NETRA/BUTA', 'CACAT RUNGU/WICARA', 'CACAT MENTAL/JIWA', 'CACAT FISIK DAN MENTAL', 'CACAT LAINNYA'
            ],
            'ref_cara_kb' => [
                'PIL', 'SUNTIK', 'IUD', 'IMPLAN', 'KONDOM', 'MOW', 'MOP', 'TIDAK MENGGUNAKAN', 'LAINNYA'
            ],
            'ref_status_rekam' => [
                'BELUM REKAM', 'SUDAH REKAM', 'BELUM WAJIB KTP'
            ],
            'ref_suku' => [
                'LAMPUNG', 'JAWA', 'SUNDA', 'BATAK', 'MINANGKABAU', 'BUGIS', 'MAKASSAR', 'BETAWI', 'DAYAK', 'BANJAR', 'MADURA', 'BALI', 'SASAK', 'MELAYU', 'LAINNYA'
            ],
            'ref_pekerjaan' => [
                'BELUM/TIDAK BEKERJA', 'MENGURUS RUMAH TANGGA', 'PELAJAR/MAHASISWA', 'PENSIUNAN', 'PEGAWAI NEGERI SIPIL (PNS)',
                'TENTARA NASIONAL INDONESIA (TNI)', 'KEPOLISIAN RI (POLRI)', 'PERDAGANGAN', 'PETANI/PEKEBUN', 'PETERNAK',
                'NELAYAN/PERIKANAN', 'INDUSTRI', 'KONSTRUKSI', 'TRANSPORTASI', 'KARYAWAN SWASTA', 'KARYAWAN BUMN',
                'KARYAWAN BUMD', 'KARYAWAN HONORER', 'BURUH HARIAN LEPAS', 'BURUH TANI/PERKEBUNAN', 'BURUH NELAYAN/PERIKANAN',
                'BURUH PETERNAKAN', 'PEMBANTU RUMAH TANGGA', 'TUKANG CUKUR', 'TUKANG LISTRIK', 'TUKANG BATU', 'TUKANG KAYU',
                'TUKANG SOL SEPATU', 'TUKANG LAS/PANDAI BESI', 'TUKANG JAHIT', 'TUKANG GIGI', 'PENATA RIAS', 'PENATA BUSANA',
                'PENATA RAMBUT', 'MEKANIK', 'SENIMAN', 'TABIB', 'PARADUKUN', 'WIRASWASTA', 'LAINNYA'
            ],
        ];

        // 2. Disable Foreign Key Checks
        Schema::disableForeignKeyConstraints();

        // 3. Backup current resident reference data (mapping names to NIK)
        $residentBackups = DB::table('penduduks')->get()->map(function ($p) {
            return [
                'nik' => $p->nik,
                'agama' => $this->getRefName('ref_agama', $p->agama_id),
                'pendidikan_kk' => $this->getRefName('ref_pendidikan', $p->pendidikan_kk_id),
                'pendidikan_sedang' => $this->getRefName('ref_pendidikan', $p->pendidikan_sedang_id),
                'pekerjaan' => $this->getRefName('ref_pekerjaan', $p->pekerjaan_id),
                'status_kawin' => $this->getRefName('ref_status_kawin', $p->status_kawin_id),
                'kk_level' => $this->getRefName('ref_hub_keluarga', $p->kk_level_id),
                'warganegara' => $this->getRefName('ref_warganegara', $p->warganegara_id),
                'golongan_darah' => $this->getRefName('ref_golongan_darah', $p->golongan_darah_id),
                'cacat' => $this->getRefName('ref_cacat', $p->cacat_id),
                'cara_kb' => $this->getRefName('ref_cara_kb', $p->cara_kb_id),
                'status_rekam' => $this->getRefName('ref_status_rekam', $p->status_rekam_id),
                'status_dasar' => $this->getRefName('ref_status_dasar', $p->status_dasar_id),
                'suku' => $this->getRefName('ref_suku', $p->suku_id),
                'jenis_kelamin' => $this->getRefName('ref_jenis_kelamin', $p->jenis_kelamin_id),
            ];
        });

        // 4. Truncate and Seed new standards
        foreach ($standards as $table => $items) {
            DB::table($table)->truncate();
            foreach ($items as $item) {
                DB::table($table)->insert([
                    'nama' => $item,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Build New ID Mappings
        $mappings = [];
        foreach ($standards as $table => $items) {
            $mappings[$table] = DB::table($table)->pluck('id', 'nama')->all();
        }

        // 6. Update Resident Data (Mapping Old Names to New IDs)
        foreach ($residentBackups as $backup) {
            DB::table('penduduks')->where('nik', $backup['nik'])->update([
                'agama_id' => $this->findNearestId($mappings['ref_agama'], $backup['agama']),
                'pendidikan_kk_id' => $this->findNearestId($mappings['ref_pendidikan'], $backup['pendidikan_kk']),
                'pendidikan_sedang_id' => $this->findNearestId($mappings['ref_pendidikan'], $backup['pendidikan_sedang']),
                'pekerjaan_id' => $this->findNearestId($mappings['ref_pekerjaan'], $backup['pekerjaan']),
                'status_kawin_id' => $this->findNearestId($mappings['ref_status_kawin'], $backup['status_kawin']),
                'kk_level_id' => $this->findNearestId($mappings['ref_hub_keluarga'], $backup['kk_level']),
                'warganegara_id' => $this->findNearestId($mappings['ref_warganegara'], $backup['warganegara']),
                'golongan_darah_id' => $this->findNearestId($mappings['ref_golongan_darah'], $backup['golongan_darah']),
                'cacat_id' => $this->findNearestId($mappings['ref_cacat'], $backup['cacat']),
                'cara_kb_id' => $this->findNearestId($mappings['ref_cara_kb'], $backup['cara_kb']),
                'status_rekam_id' => $this->findNearestId($mappings['ref_status_rekam'], $backup['status_rekam']),
                'status_dasar_id' => $this->findNearestId($mappings['ref_status_dasar'], $backup['status_dasar']),
                'suku_id' => $this->findNearestId($mappings['ref_suku'], $backup['suku']),
                'jenis_kelamin_id' => $this->findNearestId($mappings['ref_jenis_kelamin'], $backup['jenis_kelamin']),
            ]);
        }

        // 7. Re-enable Foreign Key Checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Get Ref Name safely.
     */
    private function getRefName(string $table, $id): ?string
    {
        if (!$id) return null;
        $row = DB::table($table)->where('id', $id)->first();
        return $row ? $row->nama : null;
    }

    /**
     * Find the nearest ID based on name match (case-insensitive) or default to 1 or null.
     */
    private function findNearestId(array $idMap, ?string $name): ?int
    {
        if (!$name || $name === 'N/A') return array_values($idMap)[0] ?? null;

        $upperName = strtoupper(trim($name));
        
        // Direct match
        if (isset($idMap[$upperName])) return $idMap[$upperName];

        // Partial match or common variants
        foreach ($idMap as $standardName => $id) {
            if (str_contains($upperName, $standardName) || str_contains($standardName, $upperName)) {
                return $id;
            }
        }

        // Special normalization for specific categories
        if (str_contains($upperName, 'ISLAM')) return $idMap['ISLAM'] ?? 1;
        if (str_contains($upperName, 'KRISTEN')) return $idMap['KRISTEN'] ?? 2;
        if (str_contains($upperName, 'SD')) return $idMap['TAMAT SD / SEDERAJAT'] ?? 3;
        if (str_contains($upperName, 'SMP')) return $idMap['SLTP / SEDERAJAT'] ?? 4;
        if (str_contains($upperName, 'SMA')) return $idMap['SLTA / SEDERAJAT'] ?? 5;
        if (str_contains($upperName, 'S1') || str_contains($upperName, 'SARJANA')) return $idMap['DIPLOMA IV / STRATA I'] ?? 8;

        // Default to first item (e.g. "ISLAM", "TIDAK SEKOLAH", "HIDUP")
        return array_values($idMap)[0] ?? null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse this as we truncated data.
    }
};
