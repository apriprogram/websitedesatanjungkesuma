<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = [
            'Profil Desa',
            'Kegiatan',
            'Infrastruktur',
            'Layanan Publik',
            'Wisata Lokal',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function (string $name) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return [$name => $category->id];
        });

        $newsItems = [
            [
                'title' => 'Migrasi penduduk desa memperkuat hubungan sosial',
                'summary' => 'Penduduk baru dari dusun tetangga disambut dengan gotong royong bersama warga lama.',
                'content' => 'Tim relawan desa menyiapkan rumah singgah, mengadakan pelatihan budaya dan menyalurkan informasi layanan publik demi memastikan pendatang merasa seperti di rumah sendiri.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Festival kuliner khas Tanjung Kesuma siap digelar',
                'summary' => 'Pemerintah desa membuka bazar makanan tradisional sebagai bagian dari pekan budaya.',
                'content' => 'Panitia bersama ibu-ibu PKK menyiapkan 15 stan kuliner khas, lomba masak, dan pentas seni tolak balak yang akan berlangsung selama dua hari.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Pelayanan adminduk kini bisa berjalan dua shift',
                'summary' => 'Pelayanan administrasi kependudukan memperpanjang jam operasional agar masyarakat yang bekerja bisa mengurus dokumen.',
                'content' => 'Layanan perpanjangan KTP, KK, dan akta kelahiran dibuka pagi hingga malam dengan sistem antrian daring untuk menghindari kerumunan.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Perbaikan jalan desa tahap pertama rampung',
                'summary' => 'Proyek rabat beton sepanjang 1,2 km di dusun utara resmi dioperasikan.',
                'content' => 'Badan jalan dipasangi drainase baru dan lampu tenaga surya sehingga kendaraan roda dua dan angkutan desa merasa lebih aman.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Panen raya jagung gizi tinggi untuk ketahanan pangan',
                'summary' => 'Kelompok tani berhasil memanen jagung kualitas ekspor berkat pelatihan penggunaan pupuk organik.',
                'content' => 'Dinas pertanian kabupaten memberikan pendampingan sehingga hasil panen meningkat 42% dan sebagian akan dijual ke pasar kota.',
                'category' => 'Kegiatan',
                'status' => 'draft',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Layanan posyandu bergerak melayani tiga dusun',
                'summary' => 'Tim kesehatan desa menawarkan pemeriksaan tumbuh kembang anak-anak balita secara bergilir.',
                'content' => 'Petugas membawa timbangan digital serta konseling gizi dan imunisasi door-to-door agar ibu-ibu tidak perlu datang jauh ke puskesmas.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Wisata mata air Agek ramai pengunjung akhir pekan',
                'summary' => 'Kepala desa mengatur jam kunjungan agar fasilitas tetap nyaman.',
                'content' => 'Kelompok sadar wisata membuka jasa guide lokal dan menambahkan papan informasi titik foto favorit sehingga pengalaman wisata semakin menarik.',
                'category' => 'Wisata Lokal',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Olahraga pagi mempererat tali silaturahmi warga',
                'summary' => 'Gerak jalan desa mengajak kaum muda dan lansia untuk bergerak bersama.',
                'content' => 'Kegiatan dimulai dari balai desa hingga alun-alun dan diakhiri seminari kesehatan oleh petugas puskesmas.',
                'category' => 'Kegiatan',
                'status' => 'archived',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Penggunaan CCTV di lokasi rawan banjir ditingkatkan',
                'summary' => 'Jaringan kamera membantu memantau titik genangan yang pernah menutup jalan poros.',
                'content' => 'Bendahara desa mengalokasikan anggaran gotong royong untuk pemasangan kamera dan pusat kontrol di kantor desa.',
                'category' => 'Infrastruktur',
                'status' => 'draft',
                'published_at' => now(),
            ],
            [
                'title' => 'Pembinaan UMKM olahan kopi dan kerajinan laut',
                'summary' => 'Pelaku usaha diberi akses pelatihan pengepakan dan pemasaran digital.',
                'content' => 'Desa menggandeng Dinas Perdagangan agar produk olahan kopi robusta dan kerajinan kerang dapat dipasarkan secara daring oleh koperasi lokal.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Musrenbang desa fokuskan perbaikan irigasi',
                'summary' => 'Warga menyepakati prioritas saluran sekunder agar sawah tetap terairi sepanjang musim kemarau.',
                'content' => 'Perangkat desa menggandeng konsultan teknis untuk memetakan 4 titik bocoran irigasi dan menyiapkan jadwal kerja bakti lintas RT.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Lampu jalan tenaga surya dipasang hingga dusun selatan',
                'summary' => 'Sebanyak 25 titik penerangan baru membuat jalan desa lebih aman bagi pejalan kaki dan pengendara malam hari.',
                'content' => 'Teknisi lokal memasang panel surya dan sensor otomatis, sementara warga bergotong royong menyiapkan tiang dan kabel bawah tanah.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(11),
            ],
            [
                'title' => 'Koperasi nelayan buka layanan tabungan hasil tangkap',
                'summary' => 'Skema simpanan membantu anggota mengamankan modal operasional sebelum musim angin barat.',
                'content' => 'Pengurus menyiapkan aplikasi pencatatan sederhana dan jadwal penarikan fleksibel sehingga nelayan bisa membeli solar dan es balok lebih awal.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now()->subDays(13),
            ],
            [
                'title' => 'River tubing Sungai Kesuma resmi dibuka untuk wisatawan',
                'summary' => 'Atraksi baru menambah pilihan wisata alam berbasis komunitas dengan standar keselamatan diperbarui.',
                'content' => 'Kelompok sadar wisata menyiapkan helm, pelampung bersertifikasi, serta briefing singkat sebelum pengarungan agar pengalaman wisata tetap aman.',
                'category' => 'Wisata Lokal',
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Kelas coding dasar untuk pelajar SMP desa',
                'summary' => 'Perpustakaan desa membuka lab komputer setiap akhir pekan untuk mengajarkan logika pemrograman.',
                'content' => 'Relawan mahasiswa menyiapkan modul Scratch dan Python pemula sehingga siswa bisa membuat game edukasi bertema budaya lokal.',
                'category' => 'Kegiatan',
                'status' => 'draft',
                'published_at' => now()->addDays(5),
            ],
            [
                'title' => 'Program beasiswa anak pesisir kuliah vokasi',
                'summary' => 'Pemerintah desa menanggung biaya pendaftaran dan akomodasi awal bagi tiga siswa berprestasi.',
                'content' => 'Seleksi dilakukan transparan lewat forum dusun dan dibimbing oleh guru pendamping agar calon penerima memahami komitmen belajar.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(14),
            ],
            [
                'title' => 'TNI dan warga bersihkan kanal untuk cegah karhutla',
                'summary' => 'Garis kanal dibersihkan dari semak agar jalur sekat bakar tetap siap jika musim kering ekstrem.',
                'content' => 'Kegiatan melibatkan Babinsa, Manggala Agni, dan relawan desa yang membawa pompa portabel serta tandu darurat.',
                'category' => 'Kegiatan',
                'status' => 'archived',
                'published_at' => now()->subMonths(2),
            ],
            [
                'title' => 'Revitalisasi embung menjamin cadangan air bersih',
                'summary' => 'Embung desa diperkuat dengan penahan tanah dan penyaringan sederhana sebelum dialirkan ke rumah warga.',
                'content' => 'Tim teknis memasang geomembran, membuat jalur distribusi baru ke penampungan RT, dan menjadwalkan perawatan setiap dua minggu.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(16),
            ],
            [
                'title' => 'Pasar malam UMKM kembali hadir setiap malam minggu',
                'summary' => 'Stan kuliner, kerajinan, dan permainan anak dibuka untuk menggerakkan ekonomi lokal setelah panen.',
                'content' => 'Karang taruna mengatur tata letak stan, menyediakan listrik bersama, dan menyiapkan jadwal penampilan musik akustik.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(9),
            ],
            [
                'title' => 'Layanan konsultasi hukum gratis di balai desa',
                'summary' => 'Advokat mitra desa membantu warga menyiapkan dokumen waris, perjanjian usaha, dan sengketa ringan.',
                'content' => 'Jadwal konsultasi dibuka daring dan on-site, dengan prioritas bagi keluarga rentan serta pelaku UMKM yang membutuhkan legalitas usaha.',
                'category' => 'Layanan Publik',
                'status' => 'draft',
                'published_at' => now()->addDays(2),
            ],
            [
                'title' => 'Pusat literasi digital dibuka di balai desa',
                'summary' => 'Ruang baca dan komputer publik dapat diakses gratis setiap sore.',
                'content' => 'Relawan karang taruna menyiapkan modul keamanan digital, pengenalan aplikasi pemerintahan, dan klinik pembuatan email bagi warga.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Pelatihan budidaya ikan lele bioflok untuk pemuda',
                'summary' => '10 kolam percontohan disiapkan di lahan kosong dekat kantor desa.',
                'content' => 'Instruktur dari dinas perikanan memberikan materi pakan mandiri, pengelolaan air, serta strategi penjualan ke pasar kecamatan.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Jalur sepeda desa dilengkapi marka reflektif',
                'summary' => 'Pemasangan marka malam hari menambah keamanan bagi pesepeda dan pejalan kaki.',
                'content' => 'Petugas memasang 140 titik cat reflektif dan rambu silang, serta menambah lampu tenaga surya di tikungan tajam.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(21),
            ],
            [
                'title' => 'Lomba inovasi pangan lokal antar dusun',
                'summary' => 'Peserta ditantang membuat produk olahan singkong dan pisang bernilai jual.',
                'content' => 'Juri dari UMKM kabupaten menilai kemasan, rasa, dan model bisnis sederhana untuk mempersiapkan produk masuk marketplace.',
                'category' => 'Kegiatan',
                'status' => 'draft',
                'published_at' => now()->addDays(3),
            ],
            [
                'title' => 'Penguatan sinyal internet di Posko Siaga Desa',
                'summary' => 'Repeater baru dipasang agar komunikasi darurat tetap stabil saat cuaca ekstrem.',
                'content' => 'Tim IT relawan menyetel router cadangan, membagi SSID untuk warga, dan menambahkan backup listrik UPS 1200 VA.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(17),
            ],
            [
                'title' => 'Festival film pendek bertema budaya Tanjung Kesuma',
                'summary' => 'Pelajar dan komunitas kreatif menayangkan 8 film dokumenter singkat.',
                'content' => 'Kegiatan dilengkapi diskusi bersama sutradara lokal, kelas penulisan skenario, serta voting penonton secara daring.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(19),
            ],
            [
                'title' => 'Program adopsi pohon lindung di bantaran sungai',
                'summary' => 'Setiap keluarga diminta merawat minimal satu bibit trembesi atau bambu.',
                'content' => 'Kelompok tani hutan membagikan 500 bibit, jadwal siram bergilir, dan mencatat pertumbuhan melalui aplikasi sederhana.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Sosialisasi keselamatan wisata air di Mata Air Agek',
                'summary' => 'Pengelola memperbarui SOP pelampung, helm, dan batasan usia.',
                'content' => 'Guide wajib mengikuti drill evakuasi, papan informasi jarak aman dipasang, dan check-in pengunjung memakai QR lokal.',
                'category' => 'Wisata Lokal',
                'status' => 'archived',
                'published_at' => now()->subMonths(3),
            ],
            [
                'title' => 'Bank sampah desa tambah jadwal malam hari',
                'summary' => 'Warga yang bekerja siang bisa setor plastik dan minyak jelantah usai magrib.',
                'content' => 'Pengelola memberi insentif poin yang dapat ditukar sembako dan bekerja sama dengan pengepul resmi di kecamatan.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(13),
            ],
            [
                'title' => 'Pos ronda digital uji coba di RT 03',
                'summary' => 'Absensi ronda kini menggunakan scan QR dan laporan singkat via aplikasi WA bot.',
                'content' => 'Bot mencatat jadwal hadir, mengirim ringkasan situasi malam ke grup warga, dan menyimpan data untuk evaluasi keamanan.',
                'category' => 'Profil Desa',
                'status' => 'draft',
                'published_at' => now()->addDays(6),
            ],
            [
                'title' => 'Pembersihan saluran primer jelang musim hujan',
                'summary' => 'Normalisasi parit dilakukan untuk mencegah genangan di jalur utama desa.',
                'content' => 'Alat berat dari dinas PUPR dibantu warga, fokus pada tiga titik sedimentasi dan pemasangan bronjong sederhana.',
                'category' => 'Infrastruktur',
                'status' => 'published',
                'published_at' => now()->subDays(22),
            ],
            [
                'title' => 'Pelatihan fotografi produk bagi UMKM kuliner',
                'summary' => 'Materi mencakup pencahayaan alami, styling sederhana, dan editing gratis.',
                'content' => 'Peserta mempraktikkan memotret jajanan pasar Tanjung Kesuma dengan peralatan pinjaman, lalu mengunggah ke katalog online desa.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Pojok konsultasi pajak kendaraan dibuka setiap Sabtu',
                'summary' => 'Petugas samsat keliling membantu cek data kendaraan dan pembayaran non-tunai.',
                'content' => 'Mobil layanan parkir di halaman balai desa, menyediakan informasi denda progresif dan cetak bukti bayar langsung.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(27),
            ],
            [
                'title' => 'Turnamen e-sport antar dusun ramaikan malam minggu',
                'summary' => 'Kompetisi Mobile Legends diikuti 16 tim dengan sistem gugur.',
                'content' => 'Babak final ditayangkan di aula desa, disediakan koneksi internet prioritas dan aturan anti-cheat sederhana.',
                'category' => 'Kegiatan',
                'status' => 'archived',
                'published_at' => now()->subMonths(1),
            ],
            [
                'title' => 'Rintisan kebun herbal keluarga untuk posyandu',
                'summary' => 'Ibu-ibu kader menanam jahe, kencur, dan serai untuk bahan MPASI lokal.',
                'content' => 'Setiap RT diberi 20 polybag bibit, disertai panduan perawatan serta jadwal panen bergilir untuk stok posyandu.',
                'category' => 'Profil Desa',
                'status' => 'published',
                'published_at' => now()->subDays(24),
            ],
            [
                'title' => 'Ruang kreatif remaja desa dilengkapi printer 3D',
                'summary' => 'Alat baru digunakan untuk prototipe kerajinan dan proyek sekolah.',
                'content' => 'Instruktur memperkenalkan desain dasar dengan aplikasi gratis, dilanjutkan pameran mini hasil cetak setiap akhir bulan.',
                'category' => 'Profil Desa',
                'status' => 'draft',
                'published_at' => now()->addDays(10),
            ],
            [
                'title' => 'Workshop mitigasi bencana berbasis keluarga',
                'summary' => 'Simulasi evakuasi gempa dan kebakaran diikuti 60 kepala keluarga.',
                'content' => 'BPBD kabupaten melatih penggunaan APAR, peta jalur evakuasi, serta manajemen logistik sederhana di tenda darurat.',
                'category' => 'Layanan Publik',
                'status' => 'published',
                'published_at' => now()->subDays(28),
            ],
            [
                'title' => 'Wisata sawah sunrise diperkenalkan untuk fotografer',
                'summary' => 'Paket tur pagi termasuk spot drone aman dan pemandu lokal.',
                'content' => 'Kelompok sadar wisata menyiapkan jalur pijakan, area parkir terbatas, serta aturan jarak dari lahan yang baru ditanam.',
                'category' => 'Wisata Lokal',
                'status' => 'published',
                'published_at' => now()->subDays(18)->addHours(3),
            ],
            [
                'title' => 'Lomba desain gapura antar RT sambut hari jadi desa',
                'summary' => 'Kriteria penilaian meliputi estetika, keamanan, dan penggunaan material lokal.',
                'content' => 'Panitia menyediakan anggaran bahan dasar, sementara kreativitas diserahkan ke warga dengan pendampingan arsitek muda.',
                'category' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subDays(26),
            ],
        ];

        $authorId = User::first()?->id ?? 1;

        foreach ($newsItems as $item) {
            $slug = Str::slug($item['title']);

            News::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $item['title'],
                    'summary' => $item['summary'],
                    'content' => $item['content'],
                    'category_id' => $categories[$item['category']] ?? null,
                    'author_id' => $authorId,
                    'status' => $item['status'],
                    'published_at' => $item['published_at'],
                    'views' => rand(50, 450),
                ]
            );
        }
    }
}
