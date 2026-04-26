<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agenda>
 */
class AgendaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Musyawarah Perencanaan Pembangunan Desa (Musrenbangdes)',
            'Rapat Koordinasi Bulanan Perangkat Desa',
            'Kegiatan Posyandu Balita dan Ibu Hamil',
            'Kerja Bakti Bersih Lingkungan Dusun',
            'Penyaluran Bantuan Langsung Tunai (BLT) Dana Desa',
            'Pelatihan Pemberdayaan PKK Desa',
            'Peringatan Hari Kemerdekaan RI ke-80',
            'Sosialisasi Pencegahan Demam Berdarah',
            'Rembug Stunting Tingkat Desa',
            'Bimbingan Teknis Kelompok Tani',
            'Rapat Pembentukan Panitia Pemilihan Kepala Desa',
            'Safari Jumat Pemerintah Desa',
            'Lomba Desa Tingkat Kecamatan',
            'Monitoring dan Evaluasi Pembangunan Fisik',
            'Pelatihan Digital Marketing untuk Pemuda Desa',
        ];

        $title = $this->faker->unique()->randomElement($titles);

        // Ensure user exists
        $user = User::inRandomOrder()->first();
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'title' => $title,
            'description' => "
                <p><strong>Agenda: $title</strong></p>
                <p>Kami mengundang seluruh pihak terkait untuk dapat hadir tepat waktu pada kegiatan ini.</p>
                <ul>
                    <li>Lokasi: Balai Desa Tanjung Kesuma / Tempat yang ditentukan</li>
                    <li>Pakaian: Rapi dan Sopan / Batik</li>
                    <li>Catatan: Harap membawa dokumen yang diperlukan (jika ada)</li>
                </ul>
                <p>Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
            ",
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'is_completed' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
