<?php

namespace Database\Seeders;

use App\Models\References\RefAgama;
use App\Models\References\RefCaraKb;
use App\Models\References\RefCacat;
use App\Models\References\RefGolonganDarah;
use App\Models\References\RefHubKeluarga;
use App\Models\References\RefJenisKelamin;
use App\Models\References\RefPekerjaan;
use App\Models\References\RefPendidikan;
use App\Models\References\RefStatusDasar;
use App\Models\References\RefStatusKawin;
use App\Models\References\RefStatusRekam;
use App\Models\References\RefSuku;
use App\Models\References\RefWarganegara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed(RefAgama::class, [
            'Islam',
            'Kristen',
            'Katolik',
            'Hindu',
            'Budha',
            'Khonghucu',
            'Kepercayaan Lain',
        ]);

        $this->seed(RefPendidikan::class, [
            'Tidak/Belum Sekolah',
            'Belum Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SMP/Sederajat',
            'SMA/Sederajat',
            'Diploma I/II',
            'Diploma III',
            'Diploma IV/Sarjana',
            'Magister',
            'Doktor',
        ]);

        $this->seed(RefPekerjaan::class, [
            'Belum/Tidak Bekerja',
            'Mengurus Rumah Tangga',
            'Pelajar/Mahasiswa',
            'PNS',
            'TNI/POLRI',
            'Guru/Dosen',
            'Karyawan Swasta',
            'Wiraswasta',
            'Petani',
            'Buruh Harian Lepas',
            'Nelayan',
            'Pensiunan',
            'Lainnya',
        ]);

        $this->seed(RefStatusKawin::class, [
            'Belum Kawin',
            'Kawin',
            'Cerai Hidup',
            'Cerai Mati',
        ]);

        $this->seed(RefHubKeluarga::class, [
            'Kepala Keluarga',
            'Istri',
            'Anak',
            'Famili Lain',
            'Pembantu',
            'Lainnya',
        ]);

        $this->seed(RefWarganegara::class, [
            'WNI',
            'WNA',
            'Dwi Kewarganegaraan',
        ]);

        $this->seed(RefGolonganDarah::class, [
            'A',
            'B',
            'AB',
            'O',
            'Tidak Tahu',
        ]);

        $this->seed(RefCacat::class, [
            'Tidak Cacat',
            'Cacat Fisik',
            'Cacat Netra/Buta',
            'Cacat Rungu',
            'Cacat Mental/Jiwa',
            'Cacat Ganda',
            'Cacat Lainnya',
        ]);

        $this->seed(RefCaraKb::class, [
            'Tidak Menggunakan',
            'Pil',
            'IUD/Spiral',
            'Suntik',
            'Kondom/Alat Lain',
            'MOW',
            'MOP',
            'Implan',
            'Lainnya',
        ]);

        $this->seed(RefStatusRekam::class, [
            'Belum Rekam',
            'Sudah Rekam',
            'Dalam Proses',
        ]);

        $this->seed(RefStatusDasar::class, [
            'HIDUP',
            'PINDAH',
            'MATI',
        ]);

        $this->seed(RefJenisKelamin::class, [
            'Laki-laki',
            'Perempuan',
        ]);

        $this->seed(RefSuku::class, [
            'Lampung',
            'Jawa',
            'Sunda',
            'Bali',
            'Batak',
            'Bugis',
            'Padang/Minang',
            'Tionghoa',
            'Lainnya',
        ]);
    }

    private function seed(string $modelClass, array $values): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;

        DB::table($model->getTable())->upsert(
            collect($values)->map(fn (string $value) => [
                'nama' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all(),
            ['nama'],
            ['updated_at']
        );
    }
}
