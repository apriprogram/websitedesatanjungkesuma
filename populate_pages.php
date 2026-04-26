<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;

$pages = [
    'sejarah-desa' => [
        'title' => 'Sejarah Desa Tanjung Kesuma',
        'content' => '<h3>Asal Usul Desa</h3><p>Desa Tanjung Kesuma berdiri pada bulan Mei 1953 sebagai hasil dari program transmigrasi masyarakat dari Pulau Jawa ke Lampung. Pada awal berdirinya, tokoh masyarakat yang dikenal antara lain Adam Mulyana dan Abah Sumardi (dari Jawa Barat). Desa ini mulai berkembang pesat pada tahun 1958.</p><h3>Masa Kebangkitan</h3><p>Pada tahun 1961, desa sempat mengalami masa paceklik akibat kemarau panjang, namun berkat kegigihan warganya, desa bangkit kembali pada tahun 1962. Tokoh seperti Pak Encem dikenal sebagai perintis persawahan di desa ini, sementara Pak Karno menjadi petani pertama yang sukses mengembangkan pertanian di wilayah ini.</p>'
    ],
    'wilayah-desa' => [
        'title' => 'Wilayah & Demografi',
        'content' => '<h3>Geografis</h3><p>Desa Tanjung Kesuma merupakan desa terluas di Kecamatan Purbolinggo dengan luas wilayah sekitar 6,11 km². Desa ini terbagi menjadi 6 dusun yang tertata rapi.</p><h3>Batas Wilayah</h3><ul><li>Utara: Desa Tegal Ombo</li><li>Selatan: Desa Tegal Yoso</li><li>Barat: Desa Tanjung Inten</li><li>Timur: Taman Nasional Way Kambas</li></ul><h3>Demografi</h3><p>Mayoritas penduduk Desa Tanjung Kesuma berprofesi sebagai petani padi. Selain itu, terdapat sektor ekonomi unggulan lainnya seperti peternakan ayam petelur dan industri kreatif penjahit yang menjadi penopang ekonomi keluarga.</p>'
    ],
    'visi-misi' => [
        'title' => 'Visi dan Misi Desa',
        'content' => '<h3>Visi</h3><p>"Mewujudkan Desa Tanjung Kesuma yang Mandiri, Sejahtera, dan Berakhlak Mulia melalui Tata Kelola Pemerintahan yang Transparan dan Akuntabel."</p><h3>Misi</h3><ol><li>Meningkatkan kualitas pelayanan publik yang cepat, tepat, dan transparan berbasis teknologi informasi.</li><li>Mendorong pertumbuhan ekonomi desa melalui pemberdayaan UMKM, sektor pertanian, dan peternakan.</li><li>Mewujudkan pemerataan infrastruktur desa yang berkualitas dan berkelanjutan.</li><li>Meningkatkan kualitas sumber daya manusia melalui program pendidikan, kesehatan, dan keagamaan.</li><li>Memperkuat kerukunan antarwarga dan melestarikan kearifan lokal.</li></ol>'
    ],
    'pemerintahan-desa' => [
        'title' => 'Struktur Pemerintahan Desa',
        'content' => '<h3>Pemerintah Desa Tanjung Kesuma</h3><p>Pemerintahan Desa Tanjung Kesuma dipimpin oleh seorang Kepala Desa yang dibantu oleh perangkat desa untuk menjalankan fungsi pelayanan dan administrasi.</p><h3>Susunan Organisasi</h3><ul><li><strong>Kepala Desa:</strong> Sugianto, S.H.</li><li><strong>Sekretaris Desa:</strong> (Dalam Pembaruan)</li><li><strong>Kaur Keuangan:</strong> (Dalam Pembaruan)</li><li><strong>Kaur Perencanaan:</strong> (Dalam Pembaruan)</li><li><strong>Kasi Pemerintahan:</strong> (Dalam Pembaruan)</li><li><strong>Kasi Pelayanan:</strong> (Dalam Pembaruan)</li></ul>'
    ],
    'kontak' => [
        'title' => 'Kontak Desa',
        'content' => '<h3>Hubungi Kami</h3><p>Kami siap melayani Anda. Jika ada pertanyaan atau kebutuhan informasi lebih lanjut, silakan hubungi kami melalui kanal berikut:</p><ul><li><strong>Alamat:</strong> Jl. Raya Tanjung Kesuma, Kec. Purbolinggo, Kab. Lampung Timur, Lampung</li><li><strong>Email:</strong> pemdestanjungkesuma@gmail.com</li><li><strong>Jam Operasional:</strong> Senin - Jumat (08:00 - 15:30 WIB)</li></ul><h3>Lokasi Kantor</h3><p>Kantor Desa Tanjung Kesuma terletak strategis di jalur utama desa yang mudah diakses oleh seluruh warga.</p>'
    ],
    'jam-layanan' => [
        'title' => 'Jam Layanan Publik',
        'content' => '<h3>Jadwal Pelayanan Kantor Desa</h3><p>Kantor Desa Tanjung Kesuma melayani administrasi kependudukan dan surat-menyurat pada jam berikut:</p><ul><li><strong>Senin - Kamis:</strong> 08:00 - 15:30 WIB</li><li><strong>Jumat:</strong> 08:00 - 11:30 WIB (Lanjut 13:30 - 15:30 WIB)</li><li><strong>Sabtu - Minggu:</strong> Libur</li></ul><p><em>*Untuk keperluan darurat di luar jam kerja, silakan hubungi Kepala Dusun masing-masing.</em></p>'
    ]
];

foreach ($pages as $slug => $data) {
    Page::updateOrCreate(['slug' => $slug], $data);
}

echo "Data halaman berhasil diperbarui.\n";
