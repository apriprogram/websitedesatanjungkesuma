<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private Carbon $now;

    /**
     * @var array<string,array{cleanup:string,names:array<int,string>}>
     */
    private array $referenceDefaults = [
        'ref_agama' => [
            'cleanup' => 'Agama %',
            'names' => [
                'Islam',
                'Kristen Protestan',
                'Katolik',
                'Hindu',
                'Buddha',
                'Konghucu',
                'Kepercayaan Lokal',
                'Bahai',
                'Sikh',
                'Yudaisme',
                'Shinto',
                'Jainisme',
                'Shaman Nusantara',
                'Penghayat Kepercayaan',
                'Kristen Ortodoks',
                'Islam Sunni',
                'Islam Syiah',
                'Kristen Karismatik',
                'Pagan Modern',
                'Spiritualitas Lain',
            ],
        ],
        'ref_pendidikan' => [
            'cleanup' => 'Pendidikan %',
            'names' => [
                'Tidak/Belum Sekolah',
                'PAUD',
                'TK/RA',
                'SD/MI',
                'SMP/MTs',
                'SMA/MA',
                'SMK/MAK',
                'Paket A',
                'Paket B',
                'Paket C',
                'Diploma I',
                'Diploma II',
                'Diploma III',
                'Diploma IV',
                'Sarjana (S1)',
                'Profesi',
                'Magister (S2)',
                'Doktor (S3)',
                'Pesantren',
                'Sekolah Lainnya',
            ],
        ],
        'ref_pekerjaan' => [
            'cleanup' => 'Pekerjaan %',
            'names' => [
                'Petani',
                'Peternak',
                'Nelayan',
                'Buruh Tani',
                'Buruh Harian',
                'Pegawai Negeri Sipil',
                'Pegawai Swasta',
                'Guru',
                'Tenaga Kesehatan',
                'Wiraswasta',
                'Pedagang',
                'Sopir',
                'Mahasiswa',
                'Pelajar',
                'Ibu Rumah Tangga',
                'Pekerja Lepas',
                'Montir',
                'Seniman',
                'Programer',
                'Tidak Bekerja',
            ],
        ],
        'ref_status_kawin' => [
            'cleanup' => 'Status Kawin %',
            'names' => [
                'Belum Kawin',
                'Kawin',
                'Cerai Hidup',
                'Cerai Mati',
                'Hidup Bersama',
                'Cerai Pisah',
                'Menikah Siri',
                'Menikah Secara Adat',
                'Pisah Tempat',
                'Single Parent',
                'Duda',
                'Janda',
                'Tunangan',
                'Menikah Ulang',
                'Dipisahkan Pengadilan',
                'Menikah LDR',
                'Menikah Campur',
                'Belum Jelas',
                'Tidak Diketahui',
                'Lainnya',
            ],
        ],
        'ref_hub_keluarga' => [
            'cleanup' => 'Relasi %',
            'names' => [
                'Kepala Keluarga',
                'Istri',
                'Suami',
                'Anak',
                'Menantu',
                'Cucu',
                'Orang Tua',
                'Mertua',
                'Fam Lain',
                'Saudara Kandung',
                'Paman',
                'Bibi',
                'Sepupu',
                'Keponakan',
                'Anak Angkat',
                'Orang Tua Angkat',
                'Pembantu',
                'Tamu',
                'Kerabat Dekat',
                'Anggota Lainnya',
            ],
        ],
        'ref_warganegara' => [
            'cleanup' => 'Warganegara %',
            'names' => [
                'WNI',
                'WNI Keturunan',
                'WNA',
                'Dwi Kewarganegaraan',
                'WNA Tetap',
                'WNA Sementara',
                'WNI Tinggal di Luar Negeri',
                'Penduduk Sementara',
                'Tanpa Kewarganegaraan',
                'Naturalized Citizen',
                'Permanent Resident',
                'Refugee',
                'Visa Kerja',
                'Visa Pelajar',
                'WNI Lahir di Luar Negeri',
                'WNI Kembali',
                'Eks Transmigran',
                'Eks Pekerja Migran',
                'Perwakilan Negara Asing',
                'Lainnya',
            ],
        ],
        'ref_golongan_darah' => [
            'cleanup' => 'Golongan Darah %',
            'names' => [
                'A',
                'B',
                'AB',
                'O',
                'A+',
                'A-',
                'B+',
                'B-',
                'O+',
                'O-',
                'AB+',
                'AB-',
                'A Rh Null',
                'B Rh Null',
                'Bombay',
                'Tidak Diketahui',
                'Belum Diperiksa',
                'Sedang Proses',
                'Ganda Langka',
                'Lainnya',
            ],
        ],
        'ref_cacat' => [
            'cleanup' => 'Disabilitas %',
            'names' => [
                'Tidak Ada',
                'Fisik',
                'Netra',
                'Rungu',
                'Wicara',
                'Rungu Wicara',
                'Mental',
                'Cerebral Palsy',
                'Down Syndrome',
                'Autisme',
                'ADHD',
                'Skizofrenia',
                'Lumpuh',
                'Kusta',
                'Ganda',
                'Disabilitas Lain',
                'Daksa',
                'Kecelakaan',
                'Chronic Disease',
                'Belum Diketahui',
            ],
        ],
        'ref_cara_kb' => [
            'cleanup' => 'Metode KB %',
            'names' => [
                'Tidak Menggunakan',
                'Pil KB',
                'Suntik',
                'IUD',
                'Implan',
                'Kondom',
                'MOW/Tubektomi',
                'MOP/Vasektomi',
                'Spermisida',
                'Senggama Terputus',
                'Kalender',
                'LAM',
                'Sterilisasi',
                'Menunda',
                'Coitus Reservatus',
                'Kontrasepsi Darurat',
                'Tradisional',
                'Sedang Dipertimbangkan',
                'Tidak Cocok',
                'Lainnya',
            ],
        ],
        'ref_status_rekam' => [
            'cleanup' => 'Status Rekam %',
            'names' => [
                'Belum Rekam',
                'Sudah Rekam',
                'Dalam Proses',
                'Perlu Rekam Ulang',
                'Menunggu Janji',
                'Rekam Di Luar Kota',
                'Rekam Manual',
                'Rekam Digital',
                'Valid',
                'Invalid',
                'Sementara',
                'Menunggu Validasi',
                'Perlu Lengkapi Dokumen',
                'Ditolak',
                'Sukses',
                'Gagal',
                'Tertunda',
                'Diarsipkan',
                'Tidak Diketahui',
                'Lainnya',
            ],
        ],
        'ref_status_dasar' => [
            'cleanup' => 'Status Dasar %',
            'names' => [
                'HIDUP',
                'PINDAH',
                'MATI',
                'HILANG',
                'MUTASI MASUK',
                'MUTASI KELUAR',
                'BAYI BARU LAHIR',
                'TIDAK DITEMUKAN',
                'PERGI KE LUAR NEGERI',
                'TUNGGAL',
                'HIDUP SEMENTARA',
                'BERPINDAH DOMISILI',
                'STATUS BELUM JELAS',
                'PENDUDUK BARU',
                'RESIDEN SEMENTARA',
                'TIDAK BERDOMISILI',
                'TIDAK TERDATA',
                'DALAM PROSES HUKUM',
                'DIRAWAT',
                'LAINNYA',
            ],
        ],
        'ref_jenis_kelamin' => [
            'cleanup' => 'Jenis Kelamin %',
            'names' => [
                'Laki-Laki',
                'Perempuan',
                'Non-Biner',
                'Tidak Terdefinisi',
                'Tidak Ingin Disebutkan',
                'Androgini',
                'Bigender',
                'Genderfluid',
                'Agender',
                'Interseks',
                'Trans Laki-Laki',
                'Trans Perempuan',
                'Two-Spirit',
                'Maskulin',
                'Feminin',
                'Neutrois',
                'Genderqueer',
                'Demiboy',
                'Demigirl',
                'Lainnya',
            ],
        ],
        'ref_suku' => [
            'cleanup' => 'Suku %',
            'names' => [
                'Jawa',
                'Sunda',
                'Batak',
                'Minangkabau',
                'Bugis',
                'Makassar',
                'Betawi',
                'Lampung',
                'Dayak',
                'Banjar',
                'Madura',
                'Banten',
                'Bali',
                'Sasak',
                'Melayu',
                'Ambon',
                'Papua',
                'Toraja',
                'Nias',
                'Baduy',
            ],
        ],
    ];

    private array $dusunNames = [
        'Dusun Sri Rejeki',
        'Dusun Sumber Makmur',
        'Dusun Tanjung Baru',
        'Dusun Mekar Jaya',
        'Dusun Harapan Mulya',
        'Dusun Bina Karya',
        'Dusun Suka Damai',
        'Dusun Tri Rahayu',
        'Dusun Rahayu Lestari',
        'Dusun Tegal Sari',
        'Dusun Karang Asri',
        'Dusun Karang Mulya',
        'Dusun Sumber Sari',
        'Dusun Sidodadi',
        'Dusun Sri Kuncoro',
        'Dusun Purwodadi',
        'Dusun Majapura',
        'Dusun Margomulyo',
        'Dusun Wana Sejati',
        'Dusun Sejahtera Indah',
    ];

    private array $familySeedData = [
        [
            'no_kk' => '1809030100010001',
            'dusun_index' => 0,
            'rw_index' => 0,
            'rt_index' => 0,
            'alamat' => 'Dusun Sri Rejeki RT 01/RW 01, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809030804800001',
                    'nama' => 'Slamet Riyadi',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1980-04-08',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Petani',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031204410001',
                    'nama_ayah' => 'Sumarno Riyadi',
                    'ibu_nik' => '1809035205080001',
                    'nama_ibu' => 'Sukinem Riyadi',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1980-01',
                    'akta_perkawinan' => 'AK-3201-2005-01',
                    'tanggal_perkawinan' => '2005-07-12',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-001',
                    'id_asuransi' => 'BPJS-1809-001',
                    'no_asuransi' => '0001234567891',
                    'alamat_sekarang' => 'Dusun Sri Rejeki RT 01/RW 01, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809034211830002',
                    'nama' => 'Sulastri Handayani',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1983-11-02',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Ibu Rumah Tangga',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031204560002',
                    'nama_ayah' => 'Supardi Handayani',
                    'ibu_nik' => '1809035206720003',
                    'nama_ibu' => 'Siti Munawaroh',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1983-11',
                    'akta_perkawinan' => 'AK-3201-2005-01',
                    'tanggal_perkawinan' => '2005-07-12',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Pil KB',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Valid',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-002',
                    'id_asuransi' => 'BPJS-1809-002',
                    'no_asuransi' => '0001234567892',
                                    ],
                [
                    'nik' => '1809036405070003',
                    'nama' => 'Nadya Aulia Riyadi',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Bandar Lampung',
                    'tanggal_lahir' => '2007-05-24',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Pelajar',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809030804800001',
                    'nama_ayah' => 'Slamet Riyadi',
                    'ibu_nik' => '1809034211830002',
                    'nama_ibu' => 'Sulastri Handayani',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-2007-24',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => false,
                    'status_rekam' => 'Belum Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-003',
                    'id_asuransi' => 'BPJS-1809-003',
                    'no_asuransi' => '0001234567893',
                    'alamat_sekarang' => 'Dusun Sri Rejeki RT 01/RW 01, Desa Tanjung Kesuma',
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010002',
            'dusun_index' => 1,
            'rw_index' => 1,
            'rt_index' => 1,
            'alamat' => 'Dusun Sumber Makmur RT 02/RW 02, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031203790004',
                    'nama' => 'Ahmad Zainudin',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1979-03-12',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'Sarjana (S1)',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Wiraswasta',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031305501004',
                    'nama_ayah' => 'Nasir Zainuddin',
                    'ibu_nik' => '1809035209602005',
                    'nama_ibu' => 'Siti Hawa',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1979-03',
                    'akta_perkawinan' => 'AK-3201-2004-02',
                    'tanggal_perkawinan' => '2004-05-22',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => 'P1809030004',
                    'tanggal_akhir_paspor' => '2029-09-10',
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-004',
                    'id_asuransi' => 'BPJS-1809-004',
                    'no_asuransi' => '0001234567894',
                    'alamat_sekarang' => 'Dusun Sumber Makmur RT 02/RW 02, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809036006820005',
                    'nama' => 'Nur Aisyah',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1982-06-20',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Ibu Rumah Tangga',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031505582006',
                    'nama_ayah' => 'Abdul Karim',
                    'ibu_nik' => '1809035208603007',
                    'nama_ibu' => 'Maryam',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1982-06',
                    'akta_perkawinan' => 'AK-3201-2004-02',
                    'tanggal_perkawinan' => '2004-05-22',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'IUD',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-005',
                    'id_asuransi' => 'BPJS-1809-005',
                    'no_asuransi' => '0001234567895',
                    'alamat_sekarang' => 'Dusun Sumber Makmur RT 02/RW 02, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809031412990006',
                    'nama' => 'Daffa Rizki Zainudin',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'PINDAH',
                    'tempat_lahir' => 'Bandar Lampung',
                    'tanggal_lahir' => '1999-12-14',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Mahasiswa',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031203790004',
                    'nama_ayah' => 'Ahmad Zainudin',
                    'ibu_nik' => '1809036006820005',
                    'nama_ibu' => 'Nur Aisyah',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1999-12',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Valid',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-006',
                    'id_asuransi' => 'BPJS-1809-006',
                    'no_asuransi' => '0001234567896',
                    'alamat_sekarang' => 'Kost Cahaya, Sleman, DI Yogyakarta',
                    'movement' => [
                        'tanggal_pindah' => '2023-08-15',
                        'alasan_pindah' => 'Melanjutkan studi di Universitas Negeri Yogyakarta.',
                        'alamat_tujuan' => 'Jl. Kaliurang Km 7, Kost Cahaya, Sleman',
                        'desa_tujuan' => 'Caturtunggal',
                        'kecamatan_tujuan' => 'Depok',
                        'kabupaten_tujuan' => 'Sleman',
                        'provinsi_tujuan' => 'DI Yogyakarta',
                        'keterangan' => 'Terdaftar sebagai mahasiswa baru semester ganjil 2023.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010003',
            'dusun_index' => 2,
            'rw_index' => 2,
            'rt_index' => 2,
            'alamat' => 'Dusun Tanjung Baru RT 03/RW 03, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031502750007',
                    'nama' => 'I Made Suryana',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Denpasar',
                    'tanggal_lahir' => '1975-02-15',
                    'agama' => 'Hindu',
                    'pendidikan_kk' => 'Sarjana (S1)',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Guru',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031505453008',
                    'nama_ayah' => 'I Wayan Sudarma',
                    'ibu_nik' => '1809035209423009',
                    'nama_ibu' => 'Ni Nyoman Sulastri',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1975-02',
                    'akta_perkawinan' => 'AK-3201-2008-03',
                    'tanggal_perkawinan' => '2008-03-19',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => 'P1809030007',
                    'tanggal_akhir_paspor' => '2028-05-17',
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Bali',
                    'tag_id_card' => 'TAG-1809-007',
                    'id_asuransi' => 'BPJS-1809-007',
                    'no_asuransi' => '0001234567897',
                    'alamat_sekarang' => 'Dusun Tanjung Baru RT 03/RW 03, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809035608840008',
                    'nama' => 'Komang Ayu Pratiwi',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Denpasar',
                    'tanggal_lahir' => '1984-08-16',
                    'agama' => 'Hindu',
                    'pendidikan_kk' => 'Diploma IV',
                    'pendidikan_sedang' => 'Diploma IV',
                    'pekerjaan' => 'Tenaga Kesehatan',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031504543010',
                    'nama_ayah' => 'I Nyoman Sujana',
                    'ibu_nik' => '1809035209623011',
                    'nama_ibu' => 'Ni Ketut Ariani',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1984-08',
                    'akta_perkawinan' => 'AK-3201-2008-03',
                    'tanggal_perkawinan' => '2008-03-19',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => 'P1809030008',
                    'tanggal_akhir_paspor' => '2028-05-17',
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Implan',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Bali',
                    'tag_id_card' => 'TAG-1809-008',
                    'id_asuransi' => 'BPJS-1809-008',
                    'no_asuransi' => '0001234567898',
                    'alamat_sekarang' => 'Dusun Tanjung Baru RT 03/RW 03, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809035301420009',
                    'nama' => 'Ni Luh Sulasih',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'MATI',
                    'tempat_lahir' => 'Bangli',
                    'tanggal_lahir' => '1942-01-13',
                    'agama' => 'Hindu',
                    'pendidikan_kk' => 'SD/MI',
                    'pendidikan_sedang' => 'SD/MI',
                    'pekerjaan' => 'Tidak Bekerja',
                    'status_kawin' => 'Janda',
                    'hubungan' => 'Orang Tua',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031501303012',
                    'nama_ayah' => 'I Ketut Suryawan',
                    'ibu_nik' => '1809035109303013',
                    'nama_ibu' => 'Ni Wayan Astini',
                    'golongan_darah' => 'AB',
                    'akta_lahir' => 'AL-3201-1942-01',
                    'akta_perkawinan' => 'AK-3201-1965-02',
                    'tanggal_perkawinan' => '1965-02-11',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Chronic Disease',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Diarsipkan',
                    'suku' => 'Bali',
                    'tag_id_card' => 'TAG-1809-009',
                    'id_asuransi' => 'BPJS-1809-009',
                    'no_asuransi' => '0001234567899',
                    'alamat_sekarang' => 'Dusun Tanjung Baru RT 03/RW 03, Desa Tanjung Kesuma',
                    'death' => [
                        'tanggal_meninggal' => '2024-02-10',
                        'penyebab' => 'Komplikasi kesehatan akibat usia lanjut.',
                        'tempat_meninggal' => 'RSUD Sukadana',
                        'akta_meninggal_no' => 'AM-3201-2024-001',
                        'keterangan' => 'Dimakamkan di TPU Desa Tanjung Kesuma.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010004',
            'dusun_index' => 3,
            'rw_index' => 3,
            'rt_index' => 3,
            'alamat' => 'Dusun Mekar Jaya RT 04/RW 04, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031001810010',
                    'nama' => 'Yudi Prakoso',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1981-01-10',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'Sarjana (S1)',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Pegawai Negeri Sipil',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031109523014',
                    'nama_ayah' => 'Supriyadi Prakoso',
                    'ibu_nik' => '1809035209523015',
                    'nama_ibu' => 'Sri Wahyuni',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1981-01',
                    'akta_perkawinan' => 'AK-3201-2009-04',
                    'tanggal_perkawinan' => '2009-04-25',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-010',
                    'id_asuransi' => 'BPJS-1809-010',
                    'no_asuransi' => '0001234567900',
                    'alamat_sekarang' => 'Dusun Mekar Jaya RT 04/RW 04, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809036704870011',
                    'nama' => 'Dian Anggraeni',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1987-04-27',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'Sarjana (S1)',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Guru',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031205633016',
                    'nama_ayah' => 'Sutarman',
                    'ibu_nik' => '1809035209833017',
                    'nama_ibu' => 'Kusmiati',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1987-04',
                    'akta_perkawinan' => 'AK-3201-2009-04',
                    'tanggal_perkawinan' => '2009-04-25',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Pil KB',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-011',
                    'id_asuransi' => 'BPJS-1809-011',
                    'no_asuransi' => '0001234567901',
                    'alamat_sekarang' => 'Dusun Mekar Jaya RT 04/RW 04, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809031409050012',
                    'nama' => 'Rio Prakoso',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '2005-09-14',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Pelajar',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031001810010',
                    'nama_ayah' => 'Yudi Prakoso',
                    'ibu_nik' => '1809036704870011',
                    'nama_ibu' => 'Dian Anggraeni',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-2005-09',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => false,
                    'status_rekam' => 'Dalam Proses',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-012',
                    'id_asuransi' => 'BPJS-1809-012',
                    'no_asuransi' => '0001234567902',
                    'alamat_sekarang' => 'Dusun Mekar Jaya RT 04/RW 04, Desa Tanjung Kesuma',
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010005',
            'dusun_index' => 4,
            'rw_index' => 4,
            'rt_index' => 4,
            'alamat' => 'Dusun Harapan Mulya RT 05/RW 05, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031201680013',
                    'nama' => 'Sabarudin',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1968-01-12',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMP/MTs',
                    'pendidikan_sedang' => 'SMP/MTs',
                    'pekerjaan' => 'Petani',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031204383018',
                    'nama_ayah' => 'Karimudin',
                    'ibu_nik' => '1809035209433019',
                    'nama_ibu' => 'Siti Aminah',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1968-01',
                    'akta_perkawinan' => 'AK-3201-1988-06',
                    'tanggal_perkawinan' => '1988-06-14',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-013',
                    'id_asuransi' => 'BPJS-1809-013',
                    'no_asuransi' => '0001234567903',
                    'alamat_sekarang' => 'Dusun Harapan Mulya RT 05/RW 05, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809034407700014',
                    'nama' => 'Rohana',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1970-07-04',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SD/MI',
                    'pendidikan_sedang' => 'SD/MI',
                    'pekerjaan' => 'Ibu Rumah Tangga',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031204653020',
                    'nama_ayah' => 'Samsudin',
                    'ibu_nik' => '1809035209703021',
                    'nama_ibu' => 'Hadijah',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1970-07',
                    'akta_perkawinan' => 'AK-3201-1988-06',
                    'tanggal_perkawinan' => '1988-06-14',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'IUD',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-014',
                    'id_asuransi' => 'BPJS-1809-014',
                    'no_asuransi' => '0001234567904',
                    'alamat_sekarang' => 'Dusun Harapan Mulya RT 05/RW 05, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809031806450015',
                    'nama' => 'Hasan Basri',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'MATI',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1945-06-18',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SD/MI',
                    'pendidikan_sedang' => 'SD/MI',
                    'pekerjaan' => 'Tidak Bekerja',
                    'status_kawin' => 'Cerai Mati',
                    'hubungan' => 'Orang Tua',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031501203022',
                    'nama_ayah' => 'Mahmud Basri',
                    'ibu_nik' => '1809035109203023',
                    'nama_ibu' => 'Siti Rohimah',
                    'golongan_darah' => 'AB',
                    'akta_lahir' => 'AL-3201-1945-06',
                    'akta_perkawinan' => 'AK-3201-1967-03',
                    'tanggal_perkawinan' => '1967-03-09',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Chronic Disease',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Diarsipkan',
                    'suku' => 'Lampung',
                    'tag_id_card' => 'TAG-1809-015',
                    'id_asuransi' => 'BPJS-1809-015',
                    'no_asuransi' => '0001234567905',
                    'alamat_sekarang' => 'Dusun Harapan Mulya RT 05/RW 05, Desa Tanjung Kesuma',
                    'death' => [
                        'tanggal_meninggal' => '2023-11-21',
                        'penyebab' => 'Sakit kronis yang tidak tertangani.',
                        'tempat_meninggal' => 'Rumah pribadi',
                        'akta_meninggal_no' => 'AM-3201-2023-014',
                        'keterangan' => 'Dimakamkan di TPU Harapan Mulya.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010006',
            'dusun_index' => 5,
            'rw_index' => 5,
            'rt_index' => 5,
            'alamat' => 'Dusun Bina Karya RT 06/RW 06, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809030105780016',
                    'nama' => 'Hartono Saputra',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1978-05-01',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Wiraswasta',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031205483024',
                    'nama_ayah' => 'Sukirno Saputra',
                    'ibu_nik' => '1809035209503025',
                    'nama_ibu' => 'Parmi',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1978-05',
                    'akta_perkawinan' => 'AK-3201-2000-02',
                    'tanggal_perkawinan' => '2000-02-19',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Valid',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-016',
                    'id_asuransi' => 'BPJS-1809-016',
                    'no_asuransi' => '0001234567906',
                    'alamat_sekarang' => 'Dusun Bina Karya RT 06/RW 06, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809036309810017',
                    'nama' => 'Yuliana Wulandari',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1981-09-23',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Pedagang',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031206663026',
                    'nama_ayah' => 'Parwoto',
                    'ibu_nik' => '1809035209713027',
                    'nama_ibu' => 'Suwarti',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1981-09',
                    'akta_perkawinan' => 'AK-3201-2000-02',
                    'tanggal_perkawinan' => '2000-02-19',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Suntik',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-017',
                    'id_asuransi' => 'BPJS-1809-017',
                    'no_asuransi' => '0001234567907',
                    'alamat_sekarang' => 'Dusun Bina Karya RT 06/RW 06, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809031504010018',
                    'nama' => 'Eric Saputra',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'PINDAH',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '2001-04-15',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'Diploma IV',
                    'pekerjaan' => 'Mahasiswa',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809030105780016',
                    'nama_ayah' => 'Hartono Saputra',
                    'ibu_nik' => '1809036309810017',
                    'nama_ibu' => 'Yuliana Wulandari',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-2001-04',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Menunggu Validasi',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-018',
                    'id_asuransi' => 'BPJS-1809-018',
                    'no_asuransi' => '0001234567908',
                    'alamat_sekarang' => 'Mess Program Magang, Kedaton, Bandar Lampung',
                    'movement' => [
                        'tanggal_pindah' => '2024-01-10',
                        'alasan_pindah' => 'Mengikuti program magang industri di Bandar Lampung.',
                        'alamat_tujuan' => 'Jl. Pangeran Antasari No. 18, Kedaton',
                        'desa_tujuan' => 'Kedaton',
                        'kecamatan_tujuan' => 'Kedaton',
                        'kabupaten_tujuan' => 'Bandar Lampung',
                        'provinsi_tujuan' => 'Lampung',
                        'keterangan' => 'Penugasan magang selama satu tahun.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010007',
            'dusun_index' => 6,
            'rw_index' => 6,
            'rt_index' => 6,
            'alamat' => 'Dusun Suka Damai RT 07/RW 07, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031503720019',
                    'nama' => 'Budi Laksana',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1972-03-15',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SD/MI',
                    'pendidikan_sedang' => 'SD/MI',
                    'pekerjaan' => 'Buruh Tani',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031103403028',
                    'nama_ayah' => 'Sutopo',
                    'ibu_nik' => '1809035208403029',
                    'nama_ibu' => 'Sarinem',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1972-03',
                    'akta_perkawinan' => 'AK-3201-1995-10',
                    'tanggal_perkawinan' => '1995-10-03',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-019',
                    'id_asuransi' => 'BPJS-1809-019',
                    'no_asuransi' => '0001234567909',
                    'alamat_sekarang' => 'Dusun Suka Damai RT 07/RW 07, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809036905790020',
                    'nama' => 'Lestari Puspitasari',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1979-05-29',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMP/MTs',
                    'pendidikan_sedang' => 'SMP/MTs',
                    'pekerjaan' => 'Ibu Rumah Tangga',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031206633030',
                    'nama_ayah' => 'Wardoyo',
                    'ibu_nik' => '1809035208763031',
                    'nama_ibu' => 'Kuswati',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1979-05',
                    'akta_perkawinan' => 'AK-3201-1995-10',
                    'tanggal_perkawinan' => '1995-10-03',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Implan',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-020',
                    'id_asuransi' => 'BPJS-1809-020',
                    'no_asuransi' => '0001234567910',
                    'alamat_sekarang' => 'Dusun Suka Damai RT 07/RW 07, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809034609160021',
                    'nama' => 'Aurel Putri Laksana',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '2016-09-06',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'PAUD',
                    'pendidikan_sedang' => 'PAUD',
                    'pekerjaan' => 'Pelajar',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031503720019',
                    'nama_ayah' => 'Budi Laksana',
                    'ibu_nik' => '1809036905790020',
                    'nama_ibu' => 'Lestari Puspitasari',
                    'golongan_darah' => 'AB',
                    'akta_lahir' => 'AL-3201-2016-09',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => false,
                    'status_rekam' => 'Belum Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-021',
                    'id_asuransi' => 'BPJS-1809-021',
                    'no_asuransi' => '0001234567911',
                    'alamat_sekarang' => 'Dusun Suka Damai RT 07/RW 07, Desa Tanjung Kesuma',
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010008',
            'dusun_index' => 7,
            'rw_index' => 7,
            'rt_index' => 7,
            'alamat' => 'Dusun Tri Rahayu RT 08/RW 08, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809035807750022',
                    'nama' => 'Lina Marlina',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '1975-07-18',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Wiraswasta',
                    'status_kawin' => 'Janda',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031107703032',
                    'nama_ayah' => 'Ujang Wahyudin',
                    'ibu_nik' => '1809035207703033',
                    'nama_ibu' => 'Iroh Komariah',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1975-07',
                    'akta_perkawinan' => 'AK-3201-2000-08',
                    'tanggal_perkawinan' => '2000-08-18',
                    'akta_perceraian' => 'AC-3201-2021-04',
                    'tanggal_perceraian' => '2021-04-12',
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Valid',
                    'suku' => 'Sunda',
                    'tag_id_card' => 'TAG-1809-022',
                    'id_asuransi' => 'BPJS-1809-022',
                    'no_asuransi' => '0001234567912',
                    'alamat_sekarang' => 'Dusun Tri Rahayu RT 08/RW 08, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809031111020023',
                    'nama' => 'Rizky Maulana',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '2002-11-11',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Mahasiswa',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031207753034',
                    'nama_ayah' => 'Agus Maulana',
                    'ibu_nik' => '1809035807750022',
                    'nama_ibu' => 'Lina Marlina',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-2002-11',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Menunggu Validasi',
                    'suku' => 'Sunda',
                    'tag_id_card' => 'TAG-1809-023',
                    'id_asuransi' => 'BPJS-1809-023',
                    'no_asuransi' => '0001234567913',
                    'alamat_sekarang' => 'Asrama Mahasiswa Unila, Bandar Lampung',
                ],
                [
                    'nik' => '1809032103430024',
                    'nama' => 'Suwondo',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'MATI',
                    'tempat_lahir' => 'Purworejo',
                    'tanggal_lahir' => '1943-03-21',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SD/MI',
                    'pendidikan_sedang' => 'SD/MI',
                    'pekerjaan' => 'Tidak Bekerja',
                    'status_kawin' => 'Duda',
                    'hubungan' => 'Orang Tua',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031109233035',
                    'nama_ayah' => 'Sutiman',
                    'ibu_nik' => '1809035209233036',
                    'nama_ibu' => 'Sukarsih',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1943-03',
                    'akta_perkawinan' => 'AK-3201-1964-07',
                    'tanggal_perkawinan' => '1964-07-18',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Diarsipkan',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-024',
                    'id_asuransi' => 'BPJS-1809-024',
                    'no_asuransi' => '0001234567914',
                    'alamat_sekarang' => 'Dusun Tri Rahayu RT 08/RW 08, Desa Tanjung Kesuma',
                    'death' => [
                        'tanggal_meninggal' => '2024-04-05',
                        'penyebab' => 'Serangan jantung mendadak.',
                        'tempat_meninggal' => 'Puskesmas Tanjung Kesuma',
                        'akta_meninggal_no' => 'AM-3201-2024-005',
                        'keterangan' => 'Pemakaman keluarga di Dusun Tri Rahayu.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010009',
            'dusun_index' => 8,
            'rw_index' => 8,
            'rt_index' => 8,
            'alamat' => 'Dusun Rahayu Lestari RT 09/RW 09, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809030912650025',
                    'nama' => 'Suparjo',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1965-12-09',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMP/MTs',
                    'pendidikan_sedang' => 'SMP/MTs',
                    'pekerjaan' => 'Peternak',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031307453037',
                    'nama_ayah' => 'Wardi',
                    'ibu_nik' => '1809035209453038',
                    'nama_ibu' => 'Sinem',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1965-12',
                    'akta_perkawinan' => 'AK-3201-1989-09',
                    'tanggal_perkawinan' => '1989-09-30',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-025',
                    'id_asuransi' => 'BPJS-1809-025',
                    'no_asuransi' => '0001234567915',
                    'alamat_sekarang' => 'Dusun Rahayu Lestari RT 09/RW 09, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809034302710026',
                    'nama' => 'Siti Rahmawati',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1971-02-03',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'SMA/MA',
                    'pekerjaan' => 'Ibu Rumah Tangga',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031207703039',
                    'nama_ayah' => 'Syamsudin',
                    'ibu_nik' => '1809035208723040',
                    'nama_ibu' => 'Faridah',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1971-02',
                    'akta_perkawinan' => 'AK-3201-1989-09',
                    'tanggal_perkawinan' => '1989-09-30',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Suntik',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-026',
                    'id_asuransi' => 'BPJS-1809-026',
                    'no_asuransi' => '0001234567916',
                    'alamat_sekarang' => 'Dusun Rahayu Lestari RT 09/RW 09, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809032207950027',
                    'nama' => 'Rahman Setiawan',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'PINDAH',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1995-07-22',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMA/MA',
                    'pendidikan_sedang' => 'Diploma III',
                    'pekerjaan' => 'Pegawai Swasta',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809030912650025',
                    'nama_ayah' => 'Suparjo',
                    'ibu_nik' => '1809034302710026',
                    'nama_ibu' => 'Siti Rahmawati',
                    'golongan_darah' => 'O',
                    'akta_lahir' => 'AL-3201-1995-07',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Valid',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-027',
                    'id_asuransi' => 'BPJS-1809-027',
                    'no_asuransi' => '0001234567917',
                    'alamat_sekarang' => 'Jl. Kolonel H. Burlian No. 88, Palembang',
                    'movement' => [
                        'tanggal_pindah' => '2022-09-05',
                        'alasan_pindah' => 'Ditempatkan bekerja di cabang Palembang.',
                        'alamat_tujuan' => 'Jl. Kolonel H. Burlian No. 88, Palembang',
                        'desa_tujuan' => 'Siring Agung',
                        'kecamatan_tujuan' => 'Ilir Barat I',
                        'kabupaten_tujuan' => 'Kota Palembang',
                        'provinsi_tujuan' => 'Sumatera Selatan',
                        'keterangan' => 'Kontrak kerja tiga tahun di perusahaan logistik.',
                    ],
                ],
            ],
        ],
        [
            'no_kk' => '1809030100010010',
            'dusun_index' => 9,
            'rw_index' => 9,
            'rt_index' => 9,
            'alamat' => 'Dusun Tegal Sari RT 10/RW 10, Desa Tanjung Kesuma',
            'members' => [
                [
                    'nik' => '1809031708830028',
                    'nama' => 'Mulyadi Santoso',
                    'jenis_kelamin' => 'Laki-Laki',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '1983-08-17',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'Sarjana (S1)',
                    'pendidikan_sedang' => 'Sarjana (S1)',
                    'pekerjaan' => 'Pegawai Swasta',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Kepala Keluarga',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031108533041',
                    'nama_ayah' => 'Sutrisno Santoso',
                    'ibu_nik' => '1809035209533042',
                    'nama_ibu' => 'Warsini',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-1983-08',
                    'akta_perkawinan' => 'AK-3201-2010-12',
                    'tanggal_perkawinan' => '2010-12-05',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-028',
                    'id_asuransi' => 'BPJS-1809-028',
                    'no_asuransi' => '0001234567918',
                    'alamat_sekarang' => 'Dusun Tegal Sari RT 10/RW 10, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809034512850029',
                    'nama' => 'Yuni Kartikasari',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Metro',
                    'tanggal_lahir' => '1985-12-05',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'Diploma III',
                    'pendidikan_sedang' => 'Diploma III',
                    'pekerjaan' => 'Tenaga Kesehatan',
                    'status_kawin' => 'Kawin',
                    'hubungan' => 'Istri',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031209853043',
                    'nama_ayah' => 'Sadarudin',
                    'ibu_nik' => '1809035209853044',
                    'nama_ibu' => 'Yulidar',
                    'golongan_darah' => 'B',
                    'akta_lahir' => 'AL-3201-1985-12',
                    'akta_perkawinan' => 'AK-3201-2010-12',
                    'tanggal_perkawinan' => '2010-12-05',
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Pil KB',
                    'hamil' => false,
                    'ktp_el' => true,
                    'status_rekam' => 'Sudah Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-029',
                    'id_asuransi' => 'BPJS-1809-029',
                    'no_asuransi' => '0001234567919',
                    'alamat_sekarang' => 'Dusun Tegal Sari RT 10/RW 10, Desa Tanjung Kesuma',
                ],
                [
                    'nik' => '1809035402090030',
                    'nama' => 'Nabila Salsabila',
                    'jenis_kelamin' => 'Perempuan',
                    'status_dasar' => 'HIDUP',
                    'tempat_lahir' => 'Tanjung Kesuma',
                    'tanggal_lahir' => '2009-02-14',
                    'agama' => 'Islam',
                    'pendidikan_kk' => 'SMP/MTs',
                    'pendidikan_sedang' => 'SMP/MTs',
                    'pekerjaan' => 'Pelajar',
                    'status_kawin' => 'Belum Kawin',
                    'hubungan' => 'Anak',
                    'warganegara' => 'WNI',
                    'ayah_nik' => '1809031708830028',
                    'nama_ayah' => 'Mulyadi Santoso',
                    'ibu_nik' => '1809034512850029',
                    'nama_ibu' => 'Yuni Kartikasari',
                    'golongan_darah' => 'A',
                    'akta_lahir' => 'AL-3201-2009-02',
                    'akta_perkawinan' => null,
                    'tanggal_perkawinan' => null,
                    'akta_perceraian' => null,
                    'tanggal_perceraian' => null,
                    'dokumen_pasport' => null,
                    'tanggal_akhir_paspor' => null,
                    'dokumen_kitas' => null,
                    'cacat' => 'Tidak Ada',
                    'cara_kb' => 'Tidak Menggunakan',
                    'hamil' => null,
                    'ktp_el' => false,
                    'status_rekam' => 'Belum Rekam',
                    'suku' => 'Jawa',
                    'tag_id_card' => 'TAG-1809-030',
                    'id_asuransi' => 'BPJS-1809-030',
                    'no_asuransi' => '0001234567920',
                    'alamat_sekarang' => 'Dusun Tegal Sari RT 10/RW 10, Desa Tanjung Kesuma',
                ],
            ],
        ],
    ];

    public function up(): void


    {
        $this->now = Carbon::now();

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () {
                $this->seedReferenceTables();
                $dusunIds = $this->seedDusuns();
                $rwIds = $this->seedRws($dusunIds);
                $rtIds = $this->seedRts($rwIds);
                $keluargaData = $this->seedKeluargas($dusunIds, $rwIds, $rtIds);
                $this->seedPenduduks($keluargaData, $dusunIds, $rwIds, $rtIds);
                $this->seedPendudukPindahs();
                $this->seedPendudukMeninggals();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () {
                $this->rollbackPendudukMeninggals();
                $this->rollbackPendudukPindahs();
                $this->rollbackPenduduks();
                $this->rollbackKeluargas();
                $this->rollbackRts();
                $this->rollbackRws();
                $this->rollbackDusuns();
                $this->rollbackReferenceTables();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function seedReferenceTables(): void
    {
        foreach ($this->referenceDefaults as $table => $config) {
            DB::table($table)
                ->where('nama', 'like', $config['cleanup'])
                ->delete();

            $existing = DB::table($table)
                ->whereIn('nama', $config['names'])
                ->pluck('nama')
                ->all();

            $missing = array_values(array_diff($config['names'], $existing));

            if (empty($missing)) {
                continue;
            }

            $rows = array_map(function (string $name) {
                return [
                    'nama' => $name,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }, $missing);

            DB::table($table)->insert($rows);
        }
    }

    /**
     * @return array<int,int>
     */
    private function seedDusuns(): array
    {
        DB::table('dusuns')
            ->where('nama', 'like', 'Dusun Contoh%')
            ->delete();

        $existing = DB::table('dusuns')
            ->whereIn('nama', $this->dusunNames)
            ->pluck('nama')
            ->all();

        $missing = array_values(array_diff($this->dusunNames, $existing));

        if (! empty($missing)) {
            $rows = [];
            foreach ($missing as $index => $name) {
                $rows[] = [
                    'nama' => $name,
                    'kode' => sprintf('DSN%03d', $index + 1),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }
            DB::table('dusuns')->insert($rows);
        }

        return DB::table('dusuns')
            ->whereIn('nama', $this->dusunNames)
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * @param array<int,int> $dusunIds
     * @return array<int,int>
     */
    private function seedRws(array $dusunIds): array
    {
        $countDusun = max(count($dusunIds), 1);

        DB::table('rws')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RW%03d', $i), range(1, 20)))
            ->delete();

        $existing = DB::table('rws')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RW%03d', $i), range(1, 20)))
            ->pluck('kode')
            ->all();

        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $code = sprintf('RW%03d', $i);
            if (in_array($code, $existing, true)) {
                continue;
            }

            $rows[] = [
                'dusun_id' => $dusunIds[($i - 1) % $countDusun],
                'nomor' => str_pad((($i - 1) % 10) + 1, 3, '0', STR_PAD_LEFT),
                'kode' => $code,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        if (! empty($rows)) {
            DB::table('rws')->insert($rows);
        }

        return DB::table('rws')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RW%03d', $i), range(1, 20)))
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * @param array<int,int> $rwIds
     * @return array<int,int>
     */
    private function seedRts(array $rwIds): array
    {
        $countRw = max(count($rwIds), 1);

        DB::table('rts')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RT%03d', $i), range(1, 20)))
            ->delete();

        $existing = DB::table('rts')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RT%03d', $i), range(1, 20)))
            ->pluck('kode')
            ->all();

        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $code = sprintf('RT%03d', $i);
            if (in_array($code, $existing, true)) {
                continue;
            }
            $rows[] = [
                'rw_id' => $rwIds[($i - 1) % $countRw],
                'nomor' => str_pad((($i - 1) % 12) + 1, 3, '0', STR_PAD_LEFT),
                'kode' => $code,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        if (! empty($rows)) {
            DB::table('rts')->insert($rows);
        }

        return DB::table('rts')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RT%03d', $i), range(1, 20)))
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * @param array<int,int> $dusunIds
     * @param array<int,int> $rwIds
     * @param array<int,int> $rtIds
     * @return array<int,array{no_kk:string,dusun_id:int|null,rw_id:int|null,rt_id:int|null}>
     */
    private function seedKeluargas(array $dusunIds, array $rwIds, array $rtIds): array
    {
        $familyNumbers = array_map(static fn (array $family) => $family['no_kk'], $this->familySeedData);

        if (! empty($familyNumbers)) {
            DB::table('keluargas')
                ->whereIn('no_kk', $familyNumbers)
                ->delete();
        }

        $rows = [];
        $result = [];

        $countDusun = max(count($dusunIds), 1);
        $countRw = max(count($rwIds), 1);
        $countRt = max(count($rtIds), 1);

        foreach ($this->familySeedData as $index => $family) {
            $dusunId = $dusunIds[$family['dusun_index'] % $countDusun] ?? ($dusunIds[0] ?? null);
            $rwId = $rwIds[$family['rw_index'] % $countRw] ?? ($rwIds[0] ?? null);
            $rtId = $rtIds[$family['rt_index'] % $countRt] ?? ($rtIds[0] ?? null);
            $kepalaNik = $family['members'][0]['nik'] ?? null;

            $rows[] = [
                'no_kk' => $family['no_kk'],
                'kepala_nik' => $kepalaNik,
                'alamat' => $family['alamat'],
                'dusun_id' => $dusunId,
                'rw_id' => $rwId,
                'rt_id' => $rtId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            $result[] = [
                'no_kk' => $family['no_kk'],
                'dusun_id' => $dusunId,
                'rw_id' => $rwId,
                'rt_id' => $rtId,
                'alamat' => $family['alamat'],
            ];
        }

        if (! empty($rows)) {
            DB::table('keluargas')->insert($rows);
        }

        return $result;
    }

    /**
     * @param array<int,array{no_kk:string,dusun_id:int|null,rw_id:int|null,rt_id:int|null}> $keluargaData
     * @param array<int,int> $dusunIds
     * @param array<int,int> $rwIds
     * @param array<int,int> $rtIds
     */
    private function seedPenduduks(array $keluargaData, array $dusunIds, array $rwIds, array $rtIds): void
    {
        $familyLookup = collect($keluargaData)->keyBy('no_kk');
        $nikList = collect($this->familySeedData)
            ->flatMap(
                fn (array $family) => collect($family['members'] ?? [])
                    ->pluck('nik')
                    ->filter()
            )
            ->unique()
            ->values()
            ->all();

        if (! empty($nikList)) {
            $existingIds = DB::table('penduduks')
                ->whereIn('nik', $nikList)
                ->pluck('id')
                ->all();

            if (! empty($existingIds)) {
                DB::table('penduduk_meninggals')
                    ->whereIn('penduduk_id', $existingIds)
                    ->delete();

                DB::table('penduduk_pindahs')
                    ->whereIn('penduduk_id', $existingIds)
                    ->delete();

                DB::table('penduduks')
                    ->whereIn('id', $existingIds)
                    ->delete();
            }
        }

        $statusDasarMap = DB::table('ref_status_dasar')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $jenisKelaminMap = DB::table('ref_jenis_kelamin')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $agamaMap = DB::table('ref_agama')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $pendidikanMap = DB::table('ref_pendidikan')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $pekerjaanMap = DB::table('ref_pekerjaan')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $statusKawinMap = DB::table('ref_status_kawin')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $hubunganMap = DB::table('ref_hub_keluarga')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $warganegaraMap = DB::table('ref_warganegara')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $golonganMap = DB::table('ref_golongan_darah')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $cacatMap = DB::table('ref_cacat')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $caraKbMap = DB::table('ref_cara_kb')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $statusRekamMap = DB::table('ref_status_rekam')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $sukuMap = DB::table('ref_suku')
            ->pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $name) => [strtoupper($name) => $id])
            ->all();

        $rows = [];

        foreach ($this->familySeedData as $family) {
            $familyMeta = $familyLookup[$family['no_kk']] ?? [
                'alamat' => $family['alamat'],
                'dusun_id' => $dusunIds[$family['dusun_index'] % max(count($dusunIds), 1)] ?? null,
                'rw_id' => $rwIds[$family['rw_index'] % max(count($rwIds), 1)] ?? null,
                'rt_id' => $rtIds[$family['rt_index'] % max(count($rtIds), 1)] ?? null,
            ];

            foreach ($family['members'] ?? [] as $member) {
                if (empty($member['nik']) || empty($member['nama'])) {
                    continue;
                }

                $rows[] = [
                    'no_kk' => $family['no_kk'],
                    'nik' => $member['nik'],
                    'nama' => $member['nama'],
                    'jenis_kelamin_id' => $jenisKelaminMap[strtoupper($member['jenis_kelamin'] ?? '')] ?? reset($jenisKelaminMap),
                    'tempat_lahir' => $member['tempat_lahir'] ?? $familyMeta['alamat'],
                    'tanggal_lahir' => $member['tanggal_lahir'] ?? $this->now->toDateString(),
                    'agama_id' => $agamaMap[strtoupper($member['agama'] ?? '')] ?? reset($agamaMap),
                    'pendidikan_kk_id' => $pendidikanMap[strtoupper($member['pendidikan_kk'] ?? '')] ?? reset($pendidikanMap),
                    'pendidikan_sedang_id' => $pendidikanMap[strtoupper($member['pendidikan_sedang'] ?? $member['pendidikan_kk'] ?? '')] ?? reset($pendidikanMap),
                    'pekerjaan_id' => $pekerjaanMap[strtoupper($member['pekerjaan'] ?? '')] ?? reset($pekerjaanMap),
                    'status_kawin_id' => $statusKawinMap[strtoupper($member['status_kawin'] ?? '')] ?? reset($statusKawinMap),
                    'kk_level_id' => $hubunganMap[strtoupper($member['hubungan'] ?? '')] ?? reset($hubunganMap),
                    'warganegara_id' => $warganegaraMap[strtoupper($member['warganegara'] ?? '')] ?? reset($warganegaraMap),
                    'ayah_nik' => $member['ayah_nik'] ?? null,
                    'nama_ayah' => $member['nama_ayah'] ?? null,
                    'ibu_nik' => $member['ibu_nik'] ?? null,
                    'nama_ibu' => $member['nama_ibu'] ?? null,
                    'golongan_darah_id' => $golonganMap[strtoupper($member['golongan_darah'] ?? '')] ?? reset($golonganMap),
                    'akta_lahir' => $member['akta_lahir'] ?? null,
                    'dokumen_pasport' => $member['dokumen_pasport'] ?? null,
                    'tanggal_akhir_paspor' => $member['tanggal_akhir_paspor'] ?? null,
                    'dokumen_kitas' => $member['dokumen_kitas'] ?? null,
                    'akta_perkawinan' => $member['akta_perkawinan'] ?? null,
                    'tanggal_perkawinan' => $member['tanggal_perkawinan'] ?? null,
                    'akta_perceraian' => $member['akta_perceraian'] ?? null,
                    'tanggal_perceraian' => $member['tanggal_perceraian'] ?? null,
                    'cacat_id' => $cacatMap[strtoupper($member['cacat'] ?? '')] ?? reset($cacatMap),
                    'cara_kb_id' => $caraKbMap[strtoupper($member['cara_kb'] ?? '')] ?? reset($caraKbMap),
                    'hamil' => array_key_exists('hamil', $member) ? $member['hamil'] : null,
                    'ktp_el' => (bool) ($member['ktp_el'] ?? false),
                    'status_rekam_id' => $statusRekamMap[strtoupper($member['status_rekam'] ?? '')] ?? reset($statusRekamMap),
                    'alamat' => $familyMeta['alamat'],
                    'alamat_sekarang' => $member['alamat_sekarang'] ?? $familyMeta['alamat'],
                    'dusun_id' => $familyMeta['dusun_id'],
                    'rw_id' => $familyMeta['rw_id'],
                    'rt_id' => $familyMeta['rt_id'],
                    'status_dasar_id' => $statusDasarMap[strtoupper($member['status_dasar'] ?? '')] ?? reset($statusDasarMap),
                    'suku_id' => $sukuMap[strtoupper($member['suku'] ?? '')] ?? reset($sukuMap),
                    'tag_id_card' => $member['tag_id_card'] ?? null,
                    'id_asuransi' => $member['id_asuransi'] ?? null,
                    'no_asuransi' => $member['no_asuransi'] ?? null,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }
        }

        if (! empty($rows)) {
            DB::table('penduduks')->insert($rows);
        }
    }

    private function seedPendudukPindahs(): void
    {
        $movements = collect($this->familySeedData)
            ->flatMap(function (array $family) {
                return collect($family['members'] ?? [])
                    ->filter(fn ($member) => ! empty($member['movement']))
                    ->map(fn ($member) => [
                        'nik' => $member['nik'],
                        'movement' => $member['movement'],
                    ]);
            })
            ->values();

        if ($movements->isEmpty()) {
            return;
        }

        $pendudukMap = DB::table('penduduks')
            ->whereIn('nik', $movements->pluck('nik'))
            ->pluck('id', 'nik');

        $targetIds = $pendudukMap->values()->all();

        if (! empty($targetIds)) {
            DB::table('penduduk_pindahs')
                ->whereIn('penduduk_id', $targetIds)
                ->delete();
        }

        $rows = [];

        foreach ($movements as $movementData) {
            $pendudukId = $pendudukMap[$movementData['nik']] ?? null;
            $movement = $movementData['movement'] ?? [];

            if (! $pendudukId) {
                continue;
            }

            $rows[] = [
                'penduduk_id' => $pendudukId,
                'tanggal_pindah' => $movement['tanggal_pindah'] ?? $this->now->toDateString(),
                'alasan_pindah' => $movement['alasan_pindah'] ?? null,
                'alamat_tujuan' => $movement['alamat_tujuan'] ?? null,
                'desa_tujuan' => $movement['desa_tujuan'] ?? null,
                'kecamatan_tujuan' => $movement['kecamatan_tujuan'] ?? null,
                'kabupaten_tujuan' => $movement['kabupaten_tujuan'] ?? null,
                'provinsi_tujuan' => $movement['provinsi_tujuan'] ?? null,
                'keterangan' => $movement['keterangan'] ?? null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        if (! empty($rows)) {
            DB::table('penduduk_pindahs')->insert($rows);
        }
    }

    private function seedPendudukMeninggals(): void
    {
        $deaths = collect($this->familySeedData)
            ->flatMap(function (array $family) {
                return collect($family['members'] ?? [])
                    ->filter(fn ($member) => ! empty($member['death']))
                    ->map(fn ($member) => [
                        'nik' => $member['nik'],
                        'death' => $member['death'],
                    ]);
            })
            ->values();

        if ($deaths->isEmpty()) {
            return;
        }

        $pendudukMap = DB::table('penduduks')
            ->whereIn('nik', $deaths->pluck('nik'))
            ->pluck('id', 'nik');

        $targetIds = $pendudukMap->values()->all();

        if (! empty($targetIds)) {
            DB::table('penduduk_meninggals')
                ->whereIn('penduduk_id', $targetIds)
                ->delete();
        }

        $rows = [];

        foreach ($deaths as $deathData) {
            $pendudukId = $pendudukMap[$deathData['nik']] ?? null;
            $death = $deathData['death'] ?? [];

            if (! $pendudukId) {
                continue;
            }

            $rows[] = [
                'penduduk_id' => $pendudukId,
                'tanggal_meninggal' => $death['tanggal_meninggal'] ?? $this->now->copy()->subMonths(1)->toDateString(),
                'penyebab' => $death['penyebab'] ?? null,
                'tempat_meninggal' => $death['tempat_meninggal'] ?? null,
                'akta_meninggal_no' => $death['akta_meninggal_no'] ?? sprintf('AM-%05d', $pendudukId),
                'keterangan' => $death['keterangan'] ?? null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        if (! empty($rows)) {
            DB::table('penduduk_meninggals')->insert($rows);
        }
    }

    private function rollbackPendudukPindahs(): void
    {
        $nikList = collect($this->familySeedData)
            ->flatMap(fn (array $family) => collect($family['members'] ?? [])
                ->filter(fn ($member) => ! empty($member['movement']))
                ->pluck('nik'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($nikList)) {
            return;
        }

        $pendudukIds = DB::table('penduduks')
            ->whereIn('nik', $nikList)
            ->pluck('id')
            ->all();

        if (empty($pendudukIds)) {
            return;
        }

        DB::table('penduduk_pindahs')
            ->whereIn('penduduk_id', $pendudukIds)
            ->delete();
    }

    private function rollbackPendudukMeninggals(): void
    {
        $nikList = collect($this->familySeedData)
            ->flatMap(fn (array $family) => collect($family['members'] ?? [])
                ->filter(fn ($member) => ! empty($member['death']))
                ->pluck('nik'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($nikList)) {
            return;
        }

        $pendudukIds = DB::table('penduduks')
            ->whereIn('nik', $nikList)
            ->pluck('id')
            ->all();

        if (empty($pendudukIds)) {
            return;
        }

        DB::table('penduduk_meninggals')
            ->whereIn('penduduk_id', $pendudukIds)
            ->delete();
    }

    private function rollbackReferenceTables(): void
    {
        foreach ($this->referenceDefaults as $table => $config) {
            DB::table($table)
                ->whereIn('nama', $config['names'])
                ->delete();
        }
    }

    private function rollbackDusuns(): void
    {
        DB::table('dusuns')
            ->whereIn('nama', $this->dusunNames)
            ->delete();
    }

    private function rollbackRws(): void
    {
        DB::table('rws')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RW%03d', $i), range(1, 20)))
            ->delete();
    }

    private function rollbackRts(): void
    {
        DB::table('rts')
            ->whereIn('kode', array_map(fn ($i) => sprintf('RT%03d', $i), range(1, 20)))
            ->delete();
    }

    private function rollbackKeluargas(): void
    {
        $familyNumbers = array_map(static fn (array $family) => $family['no_kk'], $this->familySeedData);

        if (empty($familyNumbers)) {
            return;
        }

        DB::table('keluargas')
            ->whereIn('no_kk', $familyNumbers)
            ->delete();
    }

    private function rollbackPenduduks(): void
    {
        $nikList = collect($this->familySeedData)
            ->flatMap(fn (array $family) => collect($family['members'] ?? [])->pluck('nik'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($nikList)) {
            return;
        }

        $pendudukIds = DB::table('penduduks')
            ->whereIn('nik', $nikList)
            ->pluck('id')
            ->all();

        if (empty($pendudukIds)) {
            return;
        }

        DB::table('penduduk_meninggals')
            ->whereIn('penduduk_id', $pendudukIds)
            ->delete();

        DB::table('penduduk_pindahs')
            ->whereIn('penduduk_id', $pendudukIds)
            ->delete();

        DB::table('penduduks')
            ->whereIn('id', $pendudukIds)
            ->delete();
    }
};



